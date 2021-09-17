<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");

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
        $_SESSION['isLoggedIn'] = true;
        $_SESSION['sn'] = $row['LName'];
        $_SESSION['givenName'] = $row['VName'];
        $_SESSION['mail'] = $row['Email'];
        $_SESSION['affiliation'] = array();
        $_SESSION['groups'] = array();
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
  // echo $headerString;
  // $headerString = "PHPSESSID=umu6majgk2sju8hc9u73v2orkg; _shibsession_64656661756c7468747470733a2f2f7777772d332e6d6163682e6b69742e6564752f7370=_9bc28bc49fcbd35ea3331d623c13fcce";
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
    $userTable = new dbTable("localhost", "mach-portal", "motor25", "mach_portal", "users");
    $webuser = $userTable->select()->conditions(array("userId" => "4"))->get(1)[0];


    $userObject = new user("webuser");
    $_SESSION["user"] = $userObject->userInformation();
    // $_SESSION["isLoggedIn"]=true;
    echo json_encode(array('error' => "not logged in", "user" => $userObject->userInformation()));
    
  } else if(!isset($_SESSION['isLoggedIn']) || !$_SESSION['isLoggedIn']){
    $_SESSION['isLoggedIn'] = true;
    $attributes = json_decode($output, true)["attributes"];
    foreach($attributes as $attribute) {
      $valName = NULL;
      $valuesList = NULL;
      foreach($attribute as $name => $values) {
        if($name=="name") {
          $valName = $values;
        } else {
          $valuesList = $values;
        }
      }
      $_SESSION[$valName] = $valuesList;
    }
    $userObject = new user($_SESSION);
    $_SESSION["user"] = $userObject->userInformation();
    echo json_encode(array('error' => NULL, 'user' => $_SESSION["user"]));
  } else {
    $userObject = new user($_SESSION);
    $_SESSION["user"] = $userObject->userInformation();
    echo json_encode(array('error' => NULL, 'user' => $_SESSION["user"]));
  }
}
?>