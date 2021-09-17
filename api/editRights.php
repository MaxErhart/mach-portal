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
  #check if method is get or post
  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    #get post request data
    $_POST = json_decode(file_get_contents("php://input"), true);

    #check if user- or group-rights should be updated
    if($_POST["user_rights"]) {
      $table = $dbSchema->selectTable("user_rights");

      #check if right needs to be deleted, updated or created
      if($_POST["mode"]=="insert") {
        $attributes = array(
          "featureId" => $_POST["featureId"],
          "userId" => $_POST["objectId"],
          "rights" => $_POST["rights"],
          "rightsTarget" => json_encode($_POST["rightsTarget"])
        );
        echo json_encode(array("id" => $table->insert($attributes)->commit()));

      } else if($_POST["mode"]=="update") {
        $attributes = array(
          "featureId" => $_POST["featureId"],
          "rightsTarget" => json_encode($_POST["rightsTarget"])
        );        
        $condition = array(
          "userRightId" => $_POST["id"]
        );
        $table->update($attributes, $condition)->commit();
      } else if($_POST["mode"]=="delete") {

      } else if($_POST["mode"]=="cleanup") {
        print_r("hi");
        $deleteCondition = array(
          "rightsTarget" => json_encode(array("users" => array(), "groups" => array()))
        );
        $table->delete($deleteCondition)->commit();
      }
    } else {
      $table = $dbSchema->selectTable("group_rights");
    
      #check if right needs to be deleted, updated or created
      if($_POST["mode"]=="insert") {
        $attributes = array(
          "featureId" => $_POST["featureId"],
          "groupId" => $_POST["objectId"],
          "rights" => $_POST["rights"],
          "rightsTarget" => json_encode($_POST["rightsTarget"])
        );
        echo json_encode(array("id" => $table->insert($attributes)->commit()));

      } else if($_POST["mode"]=="update") {
        $attributes = array(
          "featureId" => $_POST["featureId"],
          "rightsTarget" => json_encode($_POST["rightsTarget"])
        );        
        $condition = array(
          "groupRightId" => $_POST["id"]
        );
        $table->update($attributes, $condition)->commit();
      } else if($_POST["mode"]=="delete") {

      } else if($_POST["mode"]=="cleanup") {
        $deleteCondition = array(
          "rightsTarget" => json_encode(array("users" => array(), "groups" => array()))
        );
        $table->delete($deleteCondition)->commit();
      }
    }     

  } else {

  
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
  }  
  
  
} else {

  #error not allowed to edit rights
  $result = array(
    "error" => "no permissions"
  );
  echo json_encode($result);
}
 

?>