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
  $form = [];
  $formQuery = "SELECT `id`, `name` FROM `forms` WHERE `id`='".$id."'";
  $elementsQuery = "SELECT `type`, `data`, `position`, `elementId` FROM `elements` WHERE `formId`='".$id."'";
  if($result = $connection->query($formQuery)){
    while($row = $result->fetch_assoc()) {
      $form["metadata"] = $row;
    }
  }
  if($result = $connection->query($elementsQuery)){
    $form["elements"] = [];
    while($row = $result->fetch_assoc()) {
      array_push($form["elements"], $row);
    }
  }    
  return $form;
}

// if(!isset($_SESSION['isLoggedIn'])) {
  // echo json_encode(array("error" => "not logged in"));
// } else if($id == NULL) {
  // echo json_encode(array("error" => "invalid form id"));
// } else {
  echo json_encode(array_merge(array("success" => true), main($connection, $id)));
// }
?>