<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$email = $_POST["email"];
$degree = $_POST["degree"];



$emailContent = file_get_contents("D:\inetpub\MPortal\api\applicant_email_content.txt");
$emailSubject = "Entrance examination summer term 2022";
$placeholders = [
  "degree"=>$degree
];
foreach($placeholders as $placeholder=>$value) {
  $emailContent = str_replace('{{'.$placeholder.'}}', $value, $emailContent);
}
$emailContent = nl2br($emailContent, false);
$header = "Content-type: text/html; charset=iso-8859-1\n";
$header .= "From: zk-aprf@mach.kit.edu";
mail($email, $emailSubject, $emailContent, $header)


?>