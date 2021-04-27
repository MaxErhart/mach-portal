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
  $files = array();
  $defaultPath = "D:\inetpub\MPortal\dfiles\\";
  $firstName = $_SESSION["givenName"][0];
  $lastName = $_SESSION["sn"][0];
  $uniqId = uniqid();
  print_r($_POST);
  foreach($_FILES as $id => $file) {
    $filename = $firstName."_".$lastName."_".$id."_".$uniqId."_".$_FILES[$id]["name"];
    $query = "SELECT (data) from `forms`.`elements` WHERE `elementId` = '".$id."'";
    if($result = $connection->query($query)) {
      while($row = $result->fetch_assoc()) {
        $path = json_decode($row["data"], true)["path"];

      }
    }
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
  $query = "INSERT INTO `forms`.`submissions` (formId, firstName, lastName, data, files) VALUES ('".$_POST["formId"]."', '".$firstName."', '".$lastName."', '".json_encode($data, JSON_UNESCAPED_UNICODE)."', '".json_encode($files, JSON_UNESCAPED_UNICODE)."')";
  if($result = $connection->query($query)) {
    echo json_encode(array("error" => "null"));
  }
}

main($connection);

?>