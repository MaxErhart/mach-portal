<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");

$serverName = "localhost";
$dbUser = "mach-portal";
$dbPassword = "motor25";


$_POST = json_decode(file_get_contents("php://input"), true);

function deleteThesis($thesisType, $thesisMetadata, $serverName, $user, $dbPassword) {
  if($thesisMetadata["oldData"]) {
    if($thesisType == "bc") {
      $table = new dbTable($serverName, $user, $dbPassword, "user", "bc_angebot");
      $deleteConditions = array(
        "UsNr" => $thesisMetadata["UsNr"],
        "UsNr_s" => $thesisMetadata["UsNr_s"]
      );
      $table->deleteNoPK($deleteConditions)->commit();
    } else {
      $table = new dbTable($serverName, $user, $dbPassword, "user", "ma_angebot");
      $deleteConditions = array(
        "UsNr" => $thesisMetadata["UsNr"],
        "UsNr_s" => $thesisMetadata["UsNr_s"]
      );
      $table->deleteNoPK($deleteConditions)->commit();
    }
  } else {
    $table = new dbTable($serverName, $user, $dbPassword, "mach_portal", "form_submissions");
    $deleteCondition = array(
      "formSubmissionId" => $thesisMetadata["id"]
    );
    $table->delete($deleteCondition)->commit();
  }
}


# check if user has permissions to delete thesis for all theses or by thesis id 

if(isset($_POST["mode"]) && $_POST["metaData"]) {
  if($_POST["mode"]="delete") {
    if(is_array($_SESSION["user"]["rights"]["theses"]["ids"]["write"])) {
      if(in_array($_POST["metaData"]["id"], $_SESSION["user"]["rights"]["theses"]["ids"]["write"])) {
        deleteThesis($_POST["thesis"], $_POST["metaData"], $serverName, $dbUser, $dbPassword);
      }
    } else if($_SESSION["user"]["rights"]["theses"]["ids"]["write"] == "all") {
      deleteThesis($_POST["thesis"], $_POST["metaData"], $serverName, $dbUser, $dbPassword);
    }   
  }
}  
?>