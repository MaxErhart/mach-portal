<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getUsers($dbSchema) {
  $users = $dbSchema->selectTable("users")->select("userId", "firstname", "lastname")->getAll();
  return $users;
}

function getGroups($dbSchema) {
  $groups = $dbSchema->selectTable("groups")->select("groupId", "groupName")->getAll();
  return $groups;
}

// initiate new database connection
$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  
} else if($_SERVER['REQUEST_METHOD'] === 'GET'){
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if(in_array("22", $_SESSION["user"]["groups"])){
    echo json_encode(array("error" => NULL, "users" => getUsers($dbSchema), "groups" => getGroups($dbSchema))); 
  } else {
    echo json_encode(array("error" => "no permissions"));
  }
}

?>