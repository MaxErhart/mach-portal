<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function saveForm($dbSchema, $ids, $data) { 
  $elements = $data['elements'];
  $formName = $data['formName'];
  if(empty($elements)) {
    return NULL;
  } else if($formName == NULL) {
    return NULL;
  } else {
    if($data["deadline"] == "") {
      $insertAttributes = array(
        "userId" => $_SESSION["user"]["userId"],
        "formName" => $formName,
        "targetUsers" => json_encode(array("users"=>$data["targetUsers"], "groups" => $data["targetGroups"]), JSON_UNESCAPED_UNICODE),
        "multipleSubmissions" => (int)$data["multipleSubmissions"]
      );
    } else {
      $insertAttributes = array(
        "userId" => $_SESSION["user"]["userId"],
        "formName" => $formName,
        "deadline" => $data["deadline"],
        "targetUsers" => json_encode(array("users"=>$data["targetUsers"], "groups" => $data["targetGroups"]), JSON_UNESCAPED_UNICODE),
        "multipleSubmissions" => (int)$data["multipleSubmissions"]
      );
    }    

    $formId = $dbSchema->selectTable("forms")->insert($insertAttributes)->commit();
    $index = 0;
    foreach($elements as $element) {
      if($element["component"] == "FileUploadElement") {
        $pathSegments = explode("\\", $element["data"]["path"]);
        $element["data"]["path"] = "";
        foreach($pathSegments as $segment) {
          $element["data"]["path"] .= $segment."/";
        }
      }
      $insertAttributes = array(
        "formId" => $formId,
        "component" => $element["component"],
        "position" => $index,
        "elementId" => $element["elementId"],
        "data" => json_encode($element["data"], JSON_UNESCAPED_UNICODE)
      );
      $dbSchema->selectTable("form_elements")->insert($insertAttributes)->commit();
      $index++;
    }
    return $formId;
  }
}

function updateForm($dbSchema, $ids, $data) {
  $elements = $data['elements'];
  $formName = $data['formName'];
  $formId = $data['formId'];
  if(empty($elements)) {
    return NULL;
  } else if($formName == NULL) {
    return NULL;
  } else {
    if($data["deadline"] == "") {
      $updateAttributes = array(
        "formName" => $formName,
        "targetUsers" => json_encode(array("users"=>$data["targetUsers"], "groups" => $data["targetGroups"]), JSON_UNESCAPED_UNICODE),
        "multipleSubmissions" => (int)$data["multipleSubmissions"]
        
      );
    } else {
      $updateAttributes = array(
        "formName" => $formName,
        "deadline" => $data["deadline"],
        "targetUsers" => json_encode(array("users"=>$data["targetUsers"], "groups" => $data["targetGroups"]), JSON_UNESCAPED_UNICODE),
        "multipleSubmissions" => (int)$data["multipleSubmissions"]
      );
    } 

    $condition = array(
      "formId" => $formId
    );

    $dbSchema->selectTable("forms")->update($updateAttributes, $condition)->commit();
    $index = 0;
    foreach($elements as $element) {
      if(array_key_exists("formElementId", $element)) {
        if($element["component"] == "FileUploadElement") {
          $pathSegments = explode("\\", $element["data"]["path"]);
          $element["data"]["path"] = "";
          foreach($pathSegments as $segment) {
            $element["data"]["path"] .= $segment."/";
          }
        }
        $updateAttributes = array(
          "position" => $index,
          "data" => json_encode($element["data"], JSON_UNESCAPED_UNICODE)
        );
        $condition = array(
          "formElementId" => $element["formElementId"]
        );        
        $dbSchema->selectTable("form_elements")->update($updateAttributes, $condition)->commit();
        $index++;
      } else {
        if($element["component"] == "FileUploadElement") {
          $pathSegments = explode("\\", $element["data"]["path"]);
          $element["data"]["path"] = "";
          foreach($pathSegments as $segment) {
            $element["data"]["path"] .= $segment."/";
          }
        }
        $updateAttributes = array(
          "formId" => $formId,
          "component" => $element["component"],
          "position" => $index,
          "elementId" => $element["elementId"],
          "data" => json_encode($element["data"], JSON_UNESCAPED_UNICODE)
        );
        $dbSchema->selectTable("form_elements")->insert($insertAttributes)->commit();
        $index++;        
      }

    }
    return NULL;
  }
}

// initiate new database connection
$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

// check if read/write ids for viewForm have already been fetched if not save fetched ids in session
if(array_key_exists("viewForm", $_SESSION["user"]["rights"])) {
  if(!array_key_exists("ids", $_SESSION["user"]["rights"]["viewForm"])){
    $ids = $dbSchema->getUserIds($_SESSION["user"]["rights"]["viewForm"]);
    $_SESSION["user"]["rights"]["viewForm"]["ids"]=$ids;
  } else {
    $ids = $_SESSION["user"]["rights"]["viewForm"]["ids"];
  }
} else {
  $ids = NULL;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST = json_decode(file_get_contents("php://input"), true);
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if($ids == NULL){
    echo json_encode(array("error" => "user has no rights"));
  } else {
    if($_POST["mode"] == "insert") {
      echo json_encode(array("error" => NULL, "forms" => saveForm($dbSchema, $ids, $_POST)));
    } else if($_POST["mode"] == "update") {
      echo json_encode(array("error" => NULL, "forms" => updateForm($dbSchema, $ids, $_POST)));
    }
    
  }
}
?>