<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$serverName = "localhost";
$dbName = "forms";
$user = "mach-portal";
$dbPassword = "motor25";
$connection = new mysqli($serverName, $user, $dbPassword, $dbName);

function main($connection) {
  $_POST = json_decode(file_get_contents("php://input"), true);
  $elements = $_POST['elements'];
  $formName = $_POST['formName'];
  if(empty($elements)) {
    echo json_encode(array("error" => "no elements selected"));
  } else if($formName == NULL) {
    echo json_encode(array("error" => "no form name"));
  } else {

    $query = "INSERT INTO `forms`.`forms` (name) VALUES ('".$formName."')";
    if($result = $connection->query($query)) {
      echo json_encode(array("error" => NULL));
    } else { 
      echo json_encode(array("error" => $connection->error));
    }
    
    if($result = $connection->query("SELECT LAST_INSERT_ID()")) {
      while($row = $result->fetch_assoc()){
        $formForeignKey = $row["LAST_INSERT_ID()"];
      }
    }
    $index = 0;
    foreach($elements as $element) {
      if($element["type"] == "file") {
        $pathSegments = explode("\\", $element["data"]["path"]);
        $element["data"]["path"] = "";
        foreach($pathSegments as $segment) {
          $element["data"]["path"] .= $segment."/";
        }
      }
      $query = "INSERT INTO `forms`.`elements` (type, data, formId, position, elementId) VALUES ('".$element['type']."', '".json_encode($element['data'], JSON_UNESCAPED_UNICODE)."', (".$formForeignKey."), '".$index."', '".$element['id']."')";
      if($result = $connection->query($query)) {
        echo json_encode(array("error" => NULL));
      } else { 
        echo json_encode(array("error" => $connection->error));
      }
      $index++;
    }

    

  }

}

main($connection);

?>