<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

#check if user has rights to edits user/group rights
if(array_key_exists("editRights", $_SESSION["user"]["rights"])) {
  
  
  $serverName = "localhost";
  $dbUser = "mach-portal";
  $dbPassword = "motor25";
  $dbName = "mach_portal";
  $dbSchema = new dbSchema($serverName, $dbUser, $dbPassword, $dbName);

  #get all users
  $users = $dbSchema->selectTable("users")->select()->getAll(); 

  #get all groups
  $groups = $dbSchema->selectTable("groups")->select()->getAll();

  #get all features(topics)
  $features = $dbSchema->selectTable("features")->select()->getAll();

  #get all user_rights
  $colTypePairs = array(
    "rightsTarget" => "JSON",
  );  
  $user_rights = $dbSchema->selectTable("user_rights")->select()->getAll($colTypePairs);

  #get all group_rights
  $colTypePairs = array(
    "rightsTarget" => "JSON",
  );   
  $group_rights = $dbSchema->selectTable("group_rights")->select()->getAll($colTypePairs);

  $result = array(
    "users" => $users,
    "userRights" => $user_rights,
    "groups" => $groups,
    "groupRights" => $group_rights,
    "features" => $features,
    "error" => NULL
  );
  echo json_encode($result);
} else {

  #error not allowed to edit rights
  $result = array(
    "error" => "no permissions"
  );
  echo json_encode($result);
}
 

?>