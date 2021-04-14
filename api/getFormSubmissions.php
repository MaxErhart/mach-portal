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

$_POST = json_decode(file_get_contents("php://input"), true);
$id = $_POST['id'];

function main($connection, $id) {
  $submissions = [];
  $formQuery = "SELECT `id`, `name` FROM `forms` WHERE `id`='".$id."'";
  $submissionsQuery = "SELECT * FROM `submissions` WHERE `formId`='".$id."'";
  if($result = $connection->query($formQuery)){
    while($row = $result->fetch_assoc()) {      
      $submissions["metadata"] = $row;
    }
  }
  if($result = $connection->query($submissionsQuery)){
    $submissions["submissions"] = [];
    while($row = $result->fetch_assoc()) {
      $row["data"] = json_decode($row["data"]);
      $row["files"] = json_decode($row["files"]);
      $dateTime = new DateTime($row["dateOfSubmission"]);
      $dateTime = $dateTime->format('d.m.Y');
      $row["dateOfSubmission"] = $dateTime;      
      array_push($submissions["submissions"], $row);
    }
  }
  
  $elementsQuery = "SELECT `type`, `data`, `position`, `elementId` FROM `elements` WHERE";
  $first = true;
  foreach($submissions["submissions"][0]["data"] as $elementId => $value) {
    if($first) {
      $elementsQuery .= " `elementId`='".$elementId."'";
      $first = false;
    } else {
      $elementsQuery .= " OR `elementId`='".$elementId."'";
    }
  }
  foreach($submissions["submissions"][0]["files"] as $elementId => $value) {
    if($first) {
      $elementsQuery .= " `elementId`='".$elementId."'";
      $first = false;
    } else {
      $elementsQuery .= " OR `elementId`='".$elementId."'";
    }
  }
  if($result = $connection->query($elementsQuery)){
    $submissions["elements"] = [];
    while($row = $result->fetch_assoc()) {
      $row["data"] = json_decode($row["data"]);
      array_push($submissions["elements"], $row);
    }
  }

  return $submissions;
}

// if(!isset($_SESSION['isLoggedIn'])) {
//   echo json_encode(array("error" => "not logged in"));
// } else if($id == NULL) {
//   echo json_encode(array("error" => "invalid form id"));
// } else {
  echo json_encode(array_merge(array("success" => true), main($connection, $id)));
// }
?>