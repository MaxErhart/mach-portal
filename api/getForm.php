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
  
  $_POST = json_decode(file_get_contents("php://input"), true);
  $formId = $_POST['id'];

  $colValTypes = array(
    "dateOfCreation" => "date",
    "targetUsers" => "JSON"
  );
  $conditions = array(
    "formId" => $formId,
  );
  $form = array();
  $form["metadata"] = $dbSchema->selectTable("forms")->select()->conditions($conditions)->get(1, 0, $colValTypes)[0];
  $colValTypes = array(
    "data" => "JSON"
  );  
  $form["elements"] = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->getAll($colValTypes); 
  return $form;
}

if(!isset($_SESSION['isLoggedIn'])) {
  echo json_encode(array("error" => "not logged in"));
} else {
  echo json_encode(array_merge(array("error" => NULL), main()));
}
?>