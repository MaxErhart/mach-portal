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
  

  $formData = array();
  $conditions = array(
    "formId" => $formId
  );

  $colTypePairs = array(
    "data" => "JSON",
    "files" => "JSON",
    "dateOfSubmission" => "date",
    "formName" => "once"
  );
  
  $formData = $dbSchema->selectTable("form_submissions")->innerJoin("userId", "users", "userId", array("firstname", "lastname", "mail"))->innerJoin("formId", "forms", "formId", array("formName"))->select("formSubmissionId", "data", "dateOfSubmission", "files")->conditions($conditions)->getAll($colTypePairs);
  if(empty($formData)) {
    return NULL;
  }
  $formData["formName"] = $formData[0];
  unset($formData[0]);
  $formData["submissions"] = $formData[1];
  unset($formData[1]);
  


  $conditions = array(
    "elementId" => array_merge(array_keys($formData["submissions"][0]["data"]), array_keys($formData["submissions"][0]["files"]))
  );
  $colTypePairs = array(
    "data" => "JSON"
  );
  $formData["elements"] = $dbSchema->selectTable("form_elements")->select("type", "data", "position", "elementId")->orConditions($conditions)->getAll($colTypePairs);
  // print_r($formData);
  return $formData;
}

// if(!isset($_SESSION['isLoggedIn'])) {
//   echo json_encode(array("error" => "not logged in"));
// } else if($id == NULL) {
//   echo json_encode(array("error" => "invalid form id"));
// } else {
  $submissionData = main();
  if($submissionData==NULL) {
    echo json_encode(array_merge(array("errorCode" => 404)));
  } else {
    echo json_encode(array_merge(array("success" => true), $submissionData));
  }
  
// }
?>