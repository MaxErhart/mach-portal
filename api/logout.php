<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!isset($_SESSION['isLoggedIn'])) {
  echo json_encode(array("error" => "not logged in"));
} else {
  session_destroy();
  echo json_encode(array("success" => true));
}
?>