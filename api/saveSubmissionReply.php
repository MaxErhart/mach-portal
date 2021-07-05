<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
include_once("D:\inetpub\MPortal\includes\\excelExport.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function saveSubmissionReply($dbSchema, $data) {

  foreach($data["submissionIds"] as $submissionId) {
    $keyValuePairs = array(
      "formId" => $data["formId"],
      "formSubmissionId" => $submissionId,
      "replyMessage" => $data["message"]
    );
    // print_r($keyValuePairs);
    $dbSchema->selectTable("submission_replies")->insert($keyValuePairs)->commit();
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
      echo json_encode(array("error" => NULL, "submissionReplies" => saveSubmissionReply($dbSchema, $_POST)));
    }
    
  }
}

?>