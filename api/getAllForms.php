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
  $query = "SELECT `id`, `name` from `forms`.`forms`";
  $forms = array();
  if($result = $connection->query($query)){
    while($row = $result->fetch_assoc()) {
      array_push($forms, $row);
    }
  }
  return $forms;
}

if(!isset($_SESSION['isLoggedIn'])) {
  echo json_encode(array("error" => "not logged in"));
} else {
  echo json_encode(array("success" => "successfully fetched forms", "forms" => main($connection)));
}
?>