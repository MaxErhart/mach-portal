<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!isset($_SESSION['isLoggedIn'])) {
  echo json_encode(array("error" => "not logged in"));
} else {
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
  curl_setopt($curl, CURLOPT_URL, "https://www-3.mach.kit.edu/Shibboleth.sso/Logout");
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    "Cookie: ".$headerString
  ));
  $output = curl_exec($curl);
  curl_close($curl);  
  session_destroy();
  echo json_encode(array("success" => true));
}
?>