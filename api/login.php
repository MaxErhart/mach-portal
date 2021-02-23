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



if($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['isLoggedIn'])) {
// log in as guest
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
} else if($_SERVER['REQUEST_METHOD'] === 'GET') {
// check if shib logged in
  $headerString = "";
  $first = true;
  foreach($_COOKIE as $key => $value) {
    if($first) {
      $headerString .=$key."=".$value;
      $first = false;
    } else {
      $headerString .="; ".$key."=".$value;
    } 
  }
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, "https://www-3.mach.kit.edu/Shibboleth.sso/Session");
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    "Cookie: ".$headerString
  ));
  $output = curl_exec($curl);
  curl_close($curl);
  if($output == "{}\n") {
    echo json_encode(array('error' => "not logged in"));
    session_destroy();
  } else if(!isset($_SESSION['isLoggedIn'])){
    $_SESSION['isLoggedIn'] = true;
    echo json_encode(array('success' => true,'error' => NULL, 'info' => $_SESSION, 'shib' => json_decode($output)));
  } else {
    echo json_encode(array('success' => true,'error' => NULL, 'info' => $_SESSION, 'shib' => json_decode($output)));
  }
}
?>