<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function getForms($dbSchema, $ids) {
  $data = array();
  if($ids["read"]=="all") {
    $colTypePairs = array(
      "dateOfCreation" => "date",
      "deadline" => "date",
      "targetUsers" => "JSON"
    );
    $range = array(
      "deadline" => strval(date("y-m-d"))
    );        
    $forms = $dbSchema->selectTable("forms")->select()->conditions(array(), $range)->orderBy("dateOfCreation", "DESC")->getAll($colTypePairs);
    $filteredForms = array();
    if($forms!=NULL) {
      for($i=0;$i<count($forms); $i++) {
        if(in_array($_SESSION["user"]["userId"], $forms[$i]["targetUsers"]["users"])) {
          if($ids["write"] == "all") {
            $notDisplayData["write"] = true;
          } else if(in_array($forms[$i]["userId"], $ids["write"])) {
            $notDisplayData["write"] = true;
          } else {
            $notDisplayData["write"] = false;
          }
          array_push($data, array("displayData" => $forms[$i], "notDisplayData" => $notDisplayData));
        } else {
          foreach($forms[$i]["targetUsers"]["groups"] as $group) {
            
            if(in_array($group, $_SESSION["user"]["groups"])) {
              if($ids["write"] == "all") {
                $notDisplayData["write"] = true;
              } else if(in_array($forms[$i]["userId"], $ids["write"])) {
                $notDisplayData["write"] = true;
              } else {
                $notDisplayData["write"] = false;
              }
              array_push($data, array("displayData" => $forms[$i], "notDisplayData" => $notDisplayData));
              break;  
            }
          }
        }
      }
    } else {
      return NULL;
    }
   
  } else {
    $colTypePairs = array(
      "dateOfCreation" => "date",
      "deadline" => "date"
    );    
    $conditions = array(
      "userId" => $ids["read"]
    );
    $range = array(
      "deadline" => strval(date("y-m-d"))
    );
    $forms = $dbSchema->selectTable("forms")->select()->conditions($conditions, $range)->orderBy("dateOfCreation", "DESC")->getAll($colTypePairs);

    if($forms!=NULL) {
      for($i=0;$i<count($forms); $i++) {
        if(in_array($forms[$i]["userId"], $ids["write"])) {
          $notDisplayData["write"] = true;
        } else {
          $notDisplayData["write"] = false;
        }
        array_push($data, array("displayData" => $forms[$i], "notDisplayData" => $notDisplayData));
      }
    } else {
      return NULL;
    }

  }

  return $data;
}

function deleteForm($dbSchema, $formId, $ids) {
  if($ids["write"]=="all") {
    $condition = array(
      "formId" => $formId
    );
    $dbSchema->selectTable("forms")->delete($condition)->commit();    
  } else if(in_array($_SESSION["user"]["userId"], $ids["write"])) {
    $condition = array(
      "formId" => $formId
    );
    $dbSchema->selectTable("forms")->delete($condition)->commit();
  }
}

// initiate new database connection
$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

// check if read/write ids for viewForm have already been fetched if not save fetched ids in session
if($_SESSION) {
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
}


if($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if($ids == NULL){
    echo json_encode(array("error" => "user has no rights"));
  } else {
    #get post request data
    $_POST = json_decode(file_get_contents("php://input"), true);  
    if($_POST["mode"] == "delete") {
      deleteForm($dbSchema, $_POST["formId"], $ids);
    }
  }

} else {
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  } else if($ids == NULL){
    echo json_encode(array("error" => "user has no rights"));
  } else {
    echo json_encode(array("error" => NULL, "forms" => getForms($dbSchema, $ids))); 
  }
}

?>