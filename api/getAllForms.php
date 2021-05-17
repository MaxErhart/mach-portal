<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function main() {
  $serverName = "localhost";
  $dbName = "mach_portal";
  $user = "mach-portal";
  $dbPassword = "motor25";
  $dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);
  $forms = $dbSchema->selectTable("forms")->select()->getAll();
  return $forms;
}

if(!isset($_SESSION['isLoggedIn'])) {
  echo json_encode(array("error" => "not logged in"));
} else {
  echo json_encode(array("success" => "successfully fetched forms", "forms" => main()));
}
?>