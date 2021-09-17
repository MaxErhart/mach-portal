<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function submitForm($dbSchema) {
  $files = array();
  $defaultPath = "D:\inetpub\MPortal\dfiles\\";
  $userId = $_SESSION["user"]["userId"];
  $uniqId = substr(md5(uniqid(rand(), true)), 0, 16);
  
  foreach($_FILES as $id => $file) {
    $filename = $userId."_".$id."_".$uniqId."_".$_FILES[$id]["name"];
    $conditions = array(
      "elementId" => $id
    );
    $formElement = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->get(1)[0];
    $path = json_decode($formElement["data"], true)["path"];
    $pathSegments = explode("/", $path);
    $winDir = "";
    foreach($pathSegments as $segment) {
      if($segment != "") {
        $winDir.=$segment."\\";
      }
    }
    move_uploaded_file($_FILES[$id]["tmp_name"], $winDir.$filename);

    $url = "";
    $base = true;
    foreach($pathSegments as $segment) {
      if($base) {
        if($segment == "dfiles") {
          $base = false;
        }
      } else {
        if($segment != "") {
          $url .= $segment."/";
        }
      }
    }
    $files[$id] = $url.$filename;
  }
  $data = array();
  $blacklist = array("formId", "anon", "formName", "anonFormId");
  foreach($_POST as $id => $value) {
    if(!in_array($id, $blacklist)){
      $data[$id] = $value;
    }  
  }
  
  $insertAttributes = array();
  if($_POST["anon"]) {
    $insertAttributes = array(
      "formId" => $_POST["formId"],
      "userId" => $userId,
      "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
      "files" => json_encode($files, JSON_UNESCAPED_UNICODE),
      "anonSubmissionKey" => $uniqId
    );
    if($_POST["formId"]=="70") {
      $header = "Content-type: text/html; charset=iso-8859-1\n";
      $header .= "From: KIT Department of Mechanical Engineering <noreply@mach.kit.edu>\n";
      $header .= "Bcc: zk-aprf@mach.kit.edu";
      $filepath1 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\Registration for Entrance Examination Master Mach part1.txt";
      $filepath2 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\Registration for Entrance Examination Master part2.txt";
      $emailContent1 = file_get_contents($filepath1);
      $emailContent2 = file_get_contents($filepath2);
      $content = $emailContent1.$uniqId.$emailContent2;
      mail($data["34384621069161"], "Registration for Entrance Examination Master \"Mechanical Engineering\"", $content, $header);
    } else if($_POST["formId"]=="74") {
      $header = "Content-type: text/html; charset=iso-8859-1\n";
      $header .= "From: KIT Department of Mechanical Engineering <noreply@mach.kit.edu>\n";
      $header .= "Bcc: zk-aprf@mach.kit.edu";
      $filepath1 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\Registration for Entrance Examination Master Matwerk part1.txt";
      $filepath2 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\Registration for Entrance Examination Master part2.txt";
      $emailContent1 = file_get_contents($filepath1);
      $emailContent2 = file_get_contents($filepath2);
      $content = $emailContent1.$uniqId.$emailContent2;
      mail($data["9020441896981"], "Registration for Entrance Examination Master \"Material Science and Engineering\"", $content, $header);      
      
    } else {
      $header = "Content-type: text/html; charset=utf-8\n";
      $header .= "From: KIT Department of Mechanical Engineering <noreply@mach.kit.edu>\n";
      $filepath1 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\anonEmailPart1.txt";
      $filepath2 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\anonEmailPart2.txt";
      $filepath3 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\anonEmailPart3.txt";
      $filepath4 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\anonEmailPart4.txt";
      $filepath5 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\anonEmailPart5.txt";
      $filepath6 = "D:\\inetpub\\MPortal\\dfiles\\emailContent\\anonEmailPart6.txt";
      $content1 = file_get_contents($filepath1);
      $content2 = file_get_contents($filepath2);
      $content3 = file_get_contents($filepath3);
      $content4 = file_get_contents($filepath4);
      $content5 = file_get_contents($filepath5);
      $content6 = file_get_contents($filepath6);
      $content = $content1.$_POST["formName"]." ".$content2."https://www-3.mach.kit.edu/dist/#/anon/".$_POST["anonFormId"]."?tab=1".$content3.$_POST["formName"].$content4.$uniqId.$content5."dekanat@mach.kit.edu".$content6;
      mail($data["targetEmail"], "Registration for ".$_POST["formName"], $content, $header);    
    }
  } else {

    $insertAttributes = array(
      "formId" => $_POST["formId"],
      "userId" => $userId,
      "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
      "files" => json_encode($files, JSON_UNESCAPED_UNICODE)
    );
  }
  $dbSchema->selectTable("form_submissions")->insert($insertAttributes)->commit();
}

function updateSubmission($dbSchema) {
  $files = array();
  $defaultPath = "D:\inetpub\MPortal\dfiles\\";
  $userId = $_SESSION["user"]["userId"];
  $uniqId = uniqid();
  foreach($_FILES as $id => $file) {
    $filename = $userId."_".$id."_".$uniqId."_".$_FILES[$id]["name"];
    $conditions = array(
      "elementId" => $id
    );
    $formElement = $dbSchema->selectTable("form_elements")->select()->conditions($conditions)->get(1)[0];
    $path = json_decode($formElement["data"], true)["path"];
    $pathSegments = explode("/", $path);
    $winDir = "";
    foreach($pathSegments as $segment) {
      if($segment != "") {
        $winDir.=$segment."\\";
      }
    }
    move_uploaded_file($_FILES[$id]["tmp_name"], $winDir.$filename);

    $url = "";
    $base = true;
    foreach($pathSegments as $segment) {
      if($base) {
        if($segment == "dfiles") {
          $base = false;
        }
      } else {
        if($segment != "") {
          $url .= $segment."/";
        }
      }
    }
    $files[$id] = $url.$filename;
  }
  $data = array();
  foreach($_POST as $id => $value) {
    if($id != "formSubmissionId") {
      $data[$id] = $value;
    }  
  }
  $insertAttributes = array(
    "data" => json_encode($data, JSON_UNESCAPED_UNICODE),
    "files" => json_encode($files, JSON_UNESCAPED_UNICODE)
  );
  $condition = array();
  if($_SESSION["user"]["userId"] == "4") {
    $condition = array(
      "anonSubmissionKey" => $_SESSION["anonSubmissionKey"]
    );
  } else {
    $condition = array(
      "formSubmissionId" => $_POST["formSubmissionId"]
    );
  }

  $dbSchema->selectTable("form_submissions")->update($insertAttributes, $condition)->commit();
}

$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);

// check if read/write ids for viewForm have already been fetched if not save fetched ids in session
// if(array_key_exists("submissions", $_SESSION["user"]["rights"])) {
//   if(!array_key_exists("ids", $_SESSION["user"]["rights"]["submissions"])){
//     $ids = $dbSchema->getUserIds($_SESSION["user"]["rights"]["submissions"]);
//     $_SESSION["user"]["rights"]["submissions"]["ids"]=$ids;
//   } else {
//     $ids = $_SESSION["user"]["rights"]["submissions"]["ids"];
//   }
// } else {
//   $ids = NULL;
// }

if($_SERVER['REQUEST_METHOD'] === 'POST') {
  if($_POST["mode"]=="submit") {
    echo json_encode(array("error" => NULL, "submissionId" => submitForm($dbSchema)));
  } else if($_POST["mode"]=="update") {
    updateSubmission($dbSchema);
  }    
} else {
  if(!isset($_SESSION['isLoggedIn'])) {
    echo json_encode(array("error" => "not logged in"));
  }
}

?>