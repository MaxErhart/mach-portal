<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
include_once("D:\inetpub\MPortal\includes\\validate.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function deleteForm() {
  return NULL;
}

function updateForm() {
  return NULL;
}

function createForm() {
  return NULL;
}

function updateSubmission($data, $files) {
  $formSubmissionId = $data["formSubmissionId"];
  unset($data["formSubmissionId"]);
  $formId = $data["formId"];
  $userId = $_SESSION["user"]["userId"];
  $form = new Form($formId);
  foreach($files as $key=>$val) {
    $data[$key]=$val;
  }
  return json_encode($form->updateSubmission($data, $userId, $formSubmissionId));
}

function deleteSubmission($formId, $formSubmissionId) {
  return NULL;
}

function submitForm($data, $files) {
  $formId = $data["formId"];
  $userId = $_SESSION["user"]["userId"];
  $form = new Form($formId);
  foreach($files as $key=>$val) {
    $data[$key]=$val;
  }
  return json_encode($form->submit($data, $userId));
}

if($_POST["mode"] == "submit") {
  echo submitForm($_POST, $_FILES);
} else if($_POST["mode"] == "update") {
  echo updateSubmission($_POST, $_FILES);
}

?>