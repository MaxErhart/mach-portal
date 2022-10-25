<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$serverName = "localhost";
$dbName = "time_sheet";
$user = "mach-portal";
$dbPassword = "motor25";
$dbSchema = new mysqli($serverName, $user, $dbPassword, $dbName);
// print_r($_POST);
if($_GET['action']=='submit') 
{
  $name = $_POST['name'];
  $personalNumber = $_POST['personalNumber'];
  if (filter_var($personalNumber, FILTER_VALIDATE_INT)) {
    echo " Found correct Number service.";
} else {
    echo("Variable is not an integer");
    die("Not a valid Number address!");
    exit;
}
  $email = $_POST['email'];
  if(preg_match("/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/", $email)){
    echo " Found correct Email service.";
    exit;
}

else{
    echo "No match found for free Email service.";
    die("Not a valid e-mail address!");
    exit;
}
  $workingHours = $_POST['workingHours'];

  if (filter_var($workingHours, FILTER_VALIDATE_INT)) {
    echo " Found correct Number service.";
} else {
    echo("Variable is not an integer");
    die("Not a valid Number address!");
    exit;
}

  $NumberOfHours = $_POST['NumberOfHours'];

  if (filter_var($NumberOfHours, FILTER_VALIDATE_INT)) {
    echo " Found correct Number service.";
} else {
    echo("Variable is not an integer");
    die("Not a valid Number address!");
    exit;
}

  $institute = $_POST['institute'];

  $startTime = json_decode($_POST["startTime"]);
  $endTime = json_decode($_POST["endTime"]);
  $taskBeingDone = json_decode($_POST["taskBeingDone"]);
  $vacation = json_decode($_POST["vacation"]);
  $total = json_decode($_POST["total"]);


    $sql2 = "INSERT INTO info SET 
    first_name = '$name',
    email = '$email',
    contract_hours = '$NumberOfHours',
    working_hours = '$workingHours',
    institute = '$institute',
    id = '$personalNumber'
    ";
    if (mysqli_query($dbSchema, $sql2)) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql2 . "<br>" . mysqli_error($dbSchema);
    }

  $i = 0;
  foreach($startTime as $value) {
    $sql = "INSERT INTO workingflow SET 
    id = '$personalNumber',
    start_time = '$value',
    end_time = '$endTime[$i]',
    task = '$taskBeingDone[$i]',
    vacation = '$vacation[$i]',
    total = '$total[$i]',
    first_name = '$name'
    ";
    $i = $i+1;

    if (mysqli_query($dbSchema, $sql)) {
      echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($dbSchema);
    }
  }
   
}
//to delete false data
//$sql2212 = "DELETE FROM workingflow  WHERE end_time = id = '$personalNumber'";
//mysqli_query($dbSchema, $sql2212);
?>