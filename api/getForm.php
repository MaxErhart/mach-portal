<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function select($dbSchema, $data) {

  $formId = $data['id'];

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
  $form["elements"] = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->orderBy("position", "ASC")->getAll($colValTypes);
  return $form;
}

function anonSelect($dbSchema, $data) {
  $anonKey = $data["id"];
  $colValTypes = array(
    "dateOfCreation" => "date",
    "targetUsers" => "JSON"
  );
  $conditions = array(
    "anonKey" => $anonKey,
  );
  $form = array();
  $form["metadata"] = $dbSchema->selectTable("anon_forms")->innerJoin("formId", "forms", "formId", array("formId", "formName", "targetUsers", "dateOfCreation"))->select("title", "body", "supportEmail")->conditions($conditions)->get(1, 0, $colValTypes)[0];
  // print_r($form);
  $colValTypes = array(
    "data" => "JSON"
  );
  $conditions = array(
    "formId" => $form["metadata"]["formId"],
  );  
  $form["elements"] = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->orderBy("position", "ASC")->getAll($colValTypes);
  return $form;
}

$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST = json_decode(file_get_contents("php://input"), true);
  if($_POST["mode"]==="anon") {
    echo json_encode(array_merge(array("error" => NULL), anonSelect($dbSchema, $_POST)));
  } else if($_POST["mode"]==="select") {
    echo json_encode(array_merge(array("error" => NULL), select($dbSchema, $_POST)));
  }
}

// if(!isset($_SESSION['isLoggedIn'])) {
  // echo json_encode(array("error" => "not logged in"));
// } else {
  // echo json_encode(array_merge(array("error" => NULL), main()));
// }
?>