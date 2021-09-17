<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);
$lastWeek = date("y-m-d", strtotime("-7 days"));
$now = date("y-m-d");

$range = array(
  "sendDate" => array($lastWeek, $now)
);
$colTypePairs = array(
  "targetUsers" => "JSON"
);
$emails = $dbSchema->selectTable("scheduled_emails")->select()->conditions(array(), $range)->getAll($colTypePairs);

foreach($emails as $email) {
  $emailError = false;
  $users = array();
  if(!empty($email["targetUsers"]["users"])) {
    $userConditions = array(
      "userId" => $email["targetUsers"]["users"]
    );
    $users = $dbSchema->selectTable("users")->select()->conditions($userConditions)->getAll();
    $users = array_column($users, "mail");
  }
  $usersFromGroups = array();
  
  
  if(!empty($email["targetUsers"]["groups"])) {
    $groupConditions = array(
      "groupId" => $email["targetUsers"]["groups"]
    );
    $usersFromGroups = $dbSchema->selectTable("group_members")->innerJoin("userId", "users", "userId", array("mail"))->select()->conditions($groupConditions)->getAll();
    $usersFromGroups = array_column($usersFromGroups, "mail");
  }
  $users = array_merge($usersFromGroups, $users);
  $users = array_unique($users);
  foreach($users as $userEmail) {
    $filepath = substr(rtrim($email["contentFile"], "\""), 1);
    $emailContent = file_get_contents($filepath);
    
    $header = 'Content-type: text/html; charset=iso-8859-1';
    if(mail($userEmail, $email["subject"], $emailContent, $header)) {
      continue;
    } else {
      $emailError = true;
    }
  }
  if(!$emailError) {
    $condition = array(
      "scheduledEmailId" => $email["scheduledEmailId"]
    );
    $insertAttributes = array(
      "userId" => $email["userId"],
      "sendDate" => $email["sendDate"],
      "targetUsers" => json_encode($email["targetUsers"]),
      "subject" => $email["subject"],
      "formId" => $email["formId"],
      "contentFile" => $email["contentFile"]
    );
    $dbSchema->selectTable("send_emails_archive")->insert($insertAttributes)->commit();

    $dbSchema->selectTable("scheduled_emails")->delete($condition)->commit();
  }
}



?>