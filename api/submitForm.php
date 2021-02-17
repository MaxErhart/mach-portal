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
  $query = "INSERT INTO `forms`.`submissions` (formId, data) VALUES ('".$_POST["metadata"]["id"]."', '".json_encode($_POST["data"])."')";
  if($result = $connection->query($query)) {
    echo json_encode(array("error" => "null"));
  }
}

main($connection);

?>