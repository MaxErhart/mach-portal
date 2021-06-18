<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function submitForm($dbSchema) {
  $files = array();
  $defaultPath = "D:\inetpub\MPortal\dfiles\\";
  $userId = $_SESSION["user"]["userId"];
  $uniqId = uniqid();
  foreach($_FILES as $id => $file) {
    $filename = $userId."_".$id."_".$uniqId."_".$_FILES[$id]["name"];
    $conditions = array(
      "elementId" => $id
    );
    $formElement = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->get(1)[0];
    $path = json_decode($formElement["data"], true)["path"];
    $pathSegments = explode("/", $path);
    $winDir = "";
    foreach($pathSegments as $segment) {
      if($segment != "") {
        $winDir.=$segment."\\";
      }
    }
    move_uploaded_file($_FILES[$id]["tmp_name"], $winDir.$filename);

    $url = "";
    $base = true;
    foreach($pathSegments as $segment) {
      if($base) {
        if($segment == "dfiles") {
          $base = false;
        }
      } else {
        if($segment != "") {
          $url .= $segment."/";
        }
      }
    }
    $files[$id] = $url.$filename;
  }
  $data = array();
  foreach($_POST as $id => $value) {
    if($id != "formId") {
      $data[$id] = $value;
    }  
  }
  $insertAttributes = array(
    "formId" => $_POST["formId"],
    "userId" => $userId,
    "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
    "files" => json_encode($files, JSON_UNESCAPED_UNICODE)
  );
  $dbSchema->selectTable("form_submissions")->insert($insertAttributes)->commit();
}

function updateSubmission($dbSchema) {
  $files = array();
  $defaultPath = "D:\inetpub\MPortal\dfiles\\";
  $userId = $_SESSION["user"]["userId"];
  $uniqId = uniqid();
  foreach($_FILES as $id => $file) {
    $filename = $userId."_".$id."_".$uniqId."_".$_FILES[$id]["name"];
    $conditions = array(
      "elementId" => $id
    );
    $formElement = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->get(1)[0];
    $path = json_decode($formElement["data"], true)["path"];
    $pathSegments = explode("/", $path);
    $winDir = "";
    foreach($pathSegments as $segment) {
      if($segment != "") {
        $winDir.=$segment."\\";
      }
    }
    move_uploaded_file($_FILES[$id]["tmp_name"], $winDir.$filename);

    $url = "";
    $base = true;
    foreach($pathSegments as $segment) {
      if($base) {
        if($segment == "dfiles") {
          $base = false;
        }
      } else {
        if($segment != "") {
          $url .= $segment."/";
        }
      }
    }
    $files[$id] = $url.$filename;
  }
  $data = array();
  foreach($_POST as $id => $value) {
    if($id != "formSubmissionId") {
      $data[$id] = $value;
    }  
  }
  $insertAttributes = array(
    "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
    "files" => json_encode($files, JSON_UNESCAPED_UNICODE)
  );
  $condition = array(
    "formSubmissionId" => $_POST["formSubmissionId"]
  );
  print_r($insertAttributes);
  print_r($condition);
  $dbSchema->selectTable("form_submissions")->update($insertAttributes, $condition)->commit();
}

$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

// check if read/write ids for viewForm have already been fetched if not save fetched ids in session
if(array_key_exists("submissions", $_SESSION["user"]["rights"])) {
  if(!array_key_exists("ids", $_SESSION["user"]["rights"]["submissions"])){
    $ids = $dbSchema->getUserIds($_SESSION["user"]["rights"]["submissions"]);
    $_SESSION["user"]["rights"]["submissions"]["ids"]=$ids;
  } else {
    $ids = $_SESSION["user"]["rights"]["submissions"]["ids"];
  }
} else {
  $ids = NULL;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  print_r($_POST);
  print_r($_FILES);
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if($ids == NULL){
    echo json_encode(array("error" => "user has no rights"));
  } else {
    #get post request data
    // $_POST = json_decode(file_get_contents("php://input"), true);   
    
    if($_POST["mode"]=="submit") {
      echo json_encode(array("error" => NULL, "submissionId" => submitForm($dbSchema)));
    } else if($_POST["mode"]=="update") {
      updateSubmission($dbSchema);
    }    
  }
} else {
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if($ids == NULL){
    echo json_encode(array("error" => "user has no rights"));
  } else {

  }
}

?>