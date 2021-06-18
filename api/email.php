<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function scheduleEmails($dbSchema) {
  $userId = $_SESSION["user"]["userId"];
  $path = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\";
  $filename = $userId."_".date("y-m-d")."_".$_FILES["file"]["name"];
  move_uploaded_file($_FILES["file"]["tmp_name"], $path.$filename);
  if($_POST["formId"]) {
    $insertAttributes = array(
      "userId" => $userId,
      "sendDate" => $_POST["sendDate"],
      "targetUsers" => json_encode(array("users"=>json_decode($_POST["users"]), "groups" =>json_decode($_POST["groups"])), JSON_UNESCAPED_UNICODE),
      "subject" => $_POST["subject"],
      "formId" => $_POST["formId"],
      "contentFile" => json_encode($path.$filename, JSON_UNESCAPED_UNICODE)
    );
  } else {
    $insertAttributes = array(
      "userId" => $userId,
      "sendDate" => $_POST["sendDate"],
      "targetUsers" => json_encode(array("users"=>json_decode($_POST["users"]), "groups" =>json_decode($_POST["groups"])), JSON_UNESCAPED_UNICODE),
      "subject" => $_POST["subject"],
      "contentFile" => json_encode($path.$filename, JSON_UNESCAPED_UNICODE)
    );
  }

  print_r($insertAttributes);
  $dbSchema->selectTable("scheduled_emails")->insert($insertAttributes)->commit();
  return null;
}

function getEmails($dbSchema, $data) {
  $colValTypes = array(
    "sendDate" => "date",
    "targetUsers" => "JSON"
  );  
  $conditions = array(
    "formId" => $data["formId"]
  );
  $scheduledEmails = $dbSchema->selectTable("scheduled_emails")->select()->conditions($conditions)->getAll($colValTypes);
  $sendEmails = $dbSchema->selectTable("send_emails_archive")->select()->conditions($conditions)->getAll($colValTypes);
  return array(
    "scheduledEmails" => $scheduledEmails,
    "sendEmails" => $sendEmails
  );
}

// initiate new database connection
$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if(in_array("22", $_SESSION["user"]["groups"])){
    if(!$_POST) {
      $_POST = json_decode(file_get_contents("php://input"), true);
    }
    // print_r($_POST);
    // $postData = json_decode(file_get_contents("php://input"), true);
    if($_POST["mode"]=="insert") {
      echo json_encode(array("error" => NULL, "scheduleEmails" => scheduleEmails($dbSchema)));
    } else if($_POST["mode"]=="get") {
      echo json_encode(array("error" => NULL, "emails" => getEmails($dbSchema, $_POST)));
    }
     
  } else {
    echo json_encode(array("error" => "no permissions"));
  } 
}

?>