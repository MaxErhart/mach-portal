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
  $elements = $_POST['elements'];
  $formName = $_POST['formName'];
  if(empty($elements)) {
    echo json_encode(array("error" => "no elements selected"));
  } else if($formName == NULL) {
    echo json_encode(array("error" => "no form name"));
  } else {
    $insertAttributes = array(
      "formName" => $formName,
      "userId" => $_SESSION["user"]["userId"]
    );
    $formId = $dbSchema->selectTable("forms")->insert($insertAttributes)->commit();
    $index = 0;
    foreach($elements as $element) {
      if($element["type"] == "file") {
        $pathSegments = explode("\\", $element["data"]["path"]);
        $element["data"]["path"] = "";
        foreach($pathSegments as $segment) {
          $element["data"]["path"] .= $segment."/";
        }
      }
      $insertAttributes = array(
        "formId" => $formId,
        "type" => $element["type"],
        "position" => $index,
        "elementId" => $element["id"],
        "data" => json_encode($element["data"], JSON_UNESCAPED_UNICODE)
      );
      $dbSchema->selectTable("form_elements")->insert($insertAttributes)->commit();
      $index++;
    }

    

  }

}

main();

?>