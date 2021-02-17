<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$serverName = "localhost";
$dbName = "user";
$user = "mach-portal";
$dbPassword = "motor25";

$connection = new mysqli($serverName, $user, $dbPassword, $dbName);

if(!isset($_SESSION['isLoggedIn'])) {
  $_POST = json_decode(file_get_contents("php://input"), true);
  $username = $_POST['username'];
  $password = $_POST['password'];
  if ($connection->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $query = "SELECT * FROM `".$dbName."`.`user` WHERE `Us`='".$username."'";
  if($result = $connection->query($query)){
    while($row = $result->fetch_assoc()){
      if ($username !== false && md5($password) === $row["KWMD5"]) {
        $_SESSION['username'] = $row['Us'];
        $_SESSION['isAdmin'] = ($row['Admin'] == 'J');
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['lastname'] = $row['LName'];
        $_SESSION['firstname'] = $row['VName'];
        echo json_encode(array('success' => true,'error' => NULL, 'info' => $_SESSION, 'server' => $_SERVER));
      } else {
        echo json_encode(array('error' => "login failed"));
      }      
    }
  }
} else {
  echo json_encode(array('success' => true,'error' => NULL, 'info' => $_SESSION));
}

 

?>