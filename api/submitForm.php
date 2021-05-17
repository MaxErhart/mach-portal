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

main();

?>