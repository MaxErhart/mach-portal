<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
include_once("D:\inetpub\MPortal\includes\\excelExport.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function saveSubmissionFlags($dbSchema, $data) {
  if($data["removeFlag"]) {
    $keyValuePairs = array(
      "submission_flag" => NULL,
      "flag_hover_text" => NULL
    );
    $condition = array(
      "formSubmissionId" => $data["submissionIds"]
    );
    $dbSchema->selectTable("form_submissions")->update($keyValuePairs, $condition)->commit();
  } else {
    $keyValuePairs = array(
      "submission_flag" => $data["submissionFlag"],
      "flag_hover_text" => $data["flagHoverText"]
    );
    $condition = array(
      "formSubmissionId" => $data["submissionIds"]
    );
    $dbSchema->selectTable("form_submissions")->update($keyValuePairs, $condition)->commit();
  }

  return true;
}

// initiate new database connection
$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST = json_decode(file_get_contents("php://input"), true);
  
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else {
    if($_POST["mode"] == "insert") {
      echo json_encode(array("error" => NULL, "submissionFlags" => saveSubmissionFlags($dbSchema, $_POST)));
    }
    
  }
}

?>

