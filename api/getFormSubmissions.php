<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
include_once("D:\inetpub\MPortal\includes\\excelExport.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getFormSubmissions($dbSchema, $ids, $data) {
  $result = array();
  $formId = $data["formId"];
  $formData = array();
  $colTypePairs = array(
    "data" => "JSON",
    "files" => "JSON",
    "dateOfSubmission" => "date",
    "formName" => "once"
  );  
  if($ids["read"] == "all") {
    $conditions = array(
      "formId" => $formId
    );
    $formData = $dbSchema->selectTable("form_submissions")->innerJoin("userId", "users", "userId", array("firstname", "lastname", "mail", "userId"))->innerJoin("formId", "forms", "formId", array("formName"))->select("formSubmissionId", "data", "dateOfSubmission", "files")->conditions($conditions)->getAll($colTypePairs);      
    if($formData != NULL) {
      $result["formName"] = $formData[0];
      $submissions = $formData[1];
      if($submissions != NULL) {
        $result["submissions"] = array();
        for($i=0;$i<count($submissions); $i++) {
          if($ids["write"] == "all") {
            $notDisplayData["write"] = true;
          } else if(in_array($submissions[$i]["userId"], $ids["write"])) {
            $notDisplayData["write"] = true;
          } else {
            $notDisplayData["write"] = false;
          }
          array_push($result["submissions"], array("displayData" => $submissions[$i], "notDisplayData" => $notDisplayData));
        }
        // print_r($result["submissions"]);
        $conditions = array(
          "elementId" => array_merge(array_keys($result["submissions"][0]["displayData"]["data"]), array_keys($result["submissions"][0]["displayData"]["files"]))
        );
        $colTypePairs = array(
          "data" => "JSON"
        );
        $result["elements"] = $dbSchema->selectTable("form_elements")->select("component", "data", "position", "elementId")->orConditions($conditions)->getAll($colTypePairs);        
        return $result;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }
  } else {
    $conditions = array(
      "formId" => $formId,
      "userId" => $ids["read"]
    );
    $formData = $dbSchema->selectTable("form_submissions")->innerJoin("userId", "users", "userId", array("firstname", "lastname", "mail", "userId"))->innerJoin("formId", "forms", "formId", array("formName"))->select("formSubmissionId", "data", "dateOfSubmission", "files")->conditions($conditions)->getAll($colTypePairs);       
    if($formData != NULL) {
      $result["formName"] = $formData[0];
      $submissions = $formData[1];

      if($submissions != NULL) {
        $result["submissions"] = array();
        for($i=0;$i<count($submissions); $i++) {
          // print_r($submissions[$i]);
          if(in_array($submissions[$i]["userId"], $ids["write"])) {
            $notDisplayData["write"] = true;
          } else {
            $notDisplayData["write"] = false;
          }
          array_push($result["submissions"], array("displayData" => $submissions[$i], "notDisplayData" => $notDisplayData));
        }
        $conditions = array(
          "elementId" => array_merge(array_keys($result["submissions"][0]["displayData"]["data"]), array_keys($result["submissions"][0]["displayData"]["files"]))
        );
        $colTypePairs = array(
          "data" => "JSON"
        );
        $result["elements"] = $dbSchema->selectTable("form_elements")->select("component", "data", "position", "elementId")->orConditions($conditions)->getAll($colTypePairs);        
        return $result;
      } else {
        return NULL;
      }
    } else {
      return NULL;
    }  
  }
}

function deleteFormSubmission($dbSchema, $ids, $formSubmissionId, $submissionOwnerId) {
  if($ids["write"] == "all") {
    $deleteCondition = array(
      "formSubmissionId" => $formSubmissionId
    );
    $dbSchema->selectTable("form_submissions")->delete($deleteCondition)->commit();
  } else if(in_array($submissionOwnerId, $ids["write"])) {
    $deleteCondition = array(
      "formSubmissionId" => $formSubmissionId
    );
    $dbSchema->selectTable("form_submissions")->delete($deleteCondition)->commit();
  } else {
    return NULL;
  }
}

function selectSubmission($dbSchema, $ids, $formSubmissionId) {
  $conditions = array(
    "formSubmissionId" => $formSubmissionId
  );
  $colValTypes = array(
    "dateOfSubmission" => "date",
    "data" => "JSON",
    "files" => "JSON"
  );
  $formSubmission = $dbSchema->selectTable("form_submissions")->select()->conditions($conditions)->get(1, 0, $colValTypes);
  if($formSubmission != NULL) {
    $formSubmission = $formSubmission[0];
    if($ids["write"]=="all") {
      return $formSubmission;
    } else if(in_array($formSubmission["userId"], $ids["write"])) {
      return $formSubmission;
    } else {
      return NULL;
    }
  } else {
    return NULL;
  }
}

function formatForExcel($data) {
  $submissions = $data["submissions"];
  $elements = $data["elements"];
  $formName = $data["formName"];
  // print_r($elements);
  $result = array();
  foreach($submissions as $submission) {
    $row = array();
    foreach($submission["displayData"] as $key=>$val) {
      if($key != "formSubmissionId" && $key != "userId" && $key != "files") {
        if($key == "data") {
          foreach($elements as $element) {
            if($element["component"] == "SelectionElement") {
              $row[$element["data"]["labelName"]] = $element["data"]["options"][$val[$element["elementId"]]];
            } else if($element["component"] == "InputElement") {
              $row[$element["data"]["labelName"]] = $val[$element["elementId"]];
            }
          }
        } else {
          $row[$key] = $val;
        }
      }
    }
    array_push($result, $row);
  }
  // print_r($result);
  return array("data" => $result, "formName" => $formName);
}

function excelExport($filename, $data) {
  $filename = $filename."_".date("d_m_y").".xls";
  $filepath = "D:\inetpub\MPortal\dfiles\submissions\\".$filename;  
  $string = tabDelimited($data);
  file_put_contents($filepath, $string);
  return $filename;
}

// initiate new database connection
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
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if($ids == NULL){
    echo json_encode(array("error" => "user has no rights"));
  } else {
    #get post request data
    $_POST = json_decode(file_get_contents("php://input"), true);   
    if($_POST["mode"]=="select") {
      echo json_encode(array("error" => NULL, "formSubmissions" => getFormSubmissions($dbSchema, $ids, $_POST)));
    } else if($_POST["mode"]=="delete") {
      deleteFormSubmission($dbSchema, $ids, $_POST["formSubmissionId"], $_POST["submissionOwnerId"]);
    } else if($_POST["mode"]=="selectSingleSubmission") {
      echo json_encode(array("error" => NULL, "formSubmission" => selectSubmission($dbSchema, $ids, $_POST["formSubmissionId"])));  
    } else if($_POST["mode"]=="createFile") {
      $formattedFormSubmissions = formatForExcel(getFormSubmissions($dbSchema, $ids, $_POST));
      $filename = excelExport($formattedFormSubmissions["formName"], $formattedFormSubmissions["data"]);
      echo json_encode(array("error" => NULL, "filename" => $filename));      
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