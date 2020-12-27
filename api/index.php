<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo json_encode($_SESSION);
$_SESSION['visited'] = true;
?>