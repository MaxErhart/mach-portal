<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("D:\inetpub\MPortal\api\dbFramework\main.php");
include("D:\inetpub\MPortal\api\userFramework\main.php");

$serverName = "localhost";
$dbUser = "mach-portal";
$dbPassword = "motor25";


$_POST = json_decode(file_get_contents("php://input"), true);

function deleteThesis($thesisType, $thesisMetadata, $serverName, $user, $dbPassword) {
  if($thesisMetadata["oldData"]) {
    if($thesisType == "bc") {
      $schema = new dbSchema($serverName, $user, $dbPassword, "user", "bc_angebot");
      unset($thesisMetadata["oldData"]);
      $schema->deleteNoPK($thesisMetadata)->commit();
    } else {
      $schema = new dbSchema($serverName, $user, $dbPassword, "user", "ma_angebot");
      unset($thesisMetadata["oldData"]);
      $schema->deleteNoPK($thesisMetadata)->commit();
    }
  } else {
    $schema = new dbSchema($serverName, $user, $dbPassword, "forms", "submissions");
    $schema->delete($thesisMetadata["id"])->commit();
  }
}

$user = new user($_SESSION);

if($user->rightsOnTopic("theses", "write")) {
  if(isset($_POST["mode"])) {
    if($_POST["mode"]="delete") {
      deleteThesis($_POST["thesis"], $_POST["metaData"], $serverName, $dbUser, $dbPassword);
    }
  }  
}

?>