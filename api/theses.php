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
$_POST = json_decode(file_get_contents("php://input"), true);
$instDict = array(
  "1000" => "Fahrzeugsystemtechnik / Mobile Arbeitsmaschinen",
  "1011" => "Fahrzeugsystemtechnik / Bahnsystemtechnik",
  "1012" => "Fahrzeugsystemtechnik / Leichtbautechnologie",
  "1015" => "Fusionstechnologie und Reaktortechnik",
  "1017" => "Sonstige Einrichtungen des KIT",
  "999002" => "Fachgebiet Strömungsmaschinen",
  "999004" => "Arbeitswissenschaft und Betriebsorganisation",
  "999005" => "Fördertechnik und Logistiksysteme",
  "999006" => "IAM, Keramische Werkstoffe und Technologien",
  "999007" => "Institut für Kolbenmaschinen",
  "999010" => "Strömungsmechanik",
  "999011" => "Technische Mechanik",
  "999012" => "Institut für Technische Mechanik - Bereich Kontinuumsmechanik",
  "999013" => "Thermische Strömungsmaschinen",
  "999015" => "IAM, Werkstoffkunde",
  "999016" => "IAM, Angewandte Werkstoffphysik",
  "999017" => "IAM, Werkstoff- und Biomechanik",
  "999018" => "Produktentwicklung",
  "999019" => "Fahrzeugsystemtechnik / Fahrzeugtechnik",
  "999021" => "Informationsmanagement im Ingenieurwesen",
  "999022" => "Produktionstechnik",
  "999023" => "IAM, Computational Materials Science",
  "999265" => "Institut für Kern- und Energietechnik",
  "999284" => "Institut für Angewandte Thermofluidik / Bereich Cheng",
  "999287" => "Angewandte Thermofluidik / Bereich Stieglitz",
  "999289" => "IAM, Computational Materials Science / Bereich Gumbsch",
  "999290" => "IAM, Computational Materials Science / Bereich Nestler",
  "999292" => "Technische Mechanik - Bereich Dynamik",
);
$elementsDicBc = array(
  "Ansp_Email" => "63380083",
  "Ansp_Name" => "69957753",
  "VAThema" => "26819698",
  "Inst" => "52518604",
  "XInfo" => "75984480"
);
$elementsDicMa = array(
  "Ansp_Email" => "11145252",
  "Ansp_Name" => "90460036",
  "VAThema" => "53165429",
  "Inst" => "44193424",
  "XInfo" => "82199105"
);
$table = NULL;
if(isset($_POST["thesis"])) {
  if($_POST["thesis"] == "bc") {
    $table = "bc_angebot";
    $formId = "65";
    $elementsDic = $elementsDicBc;
  } else if($_POST["thesis"] == "ma") {
    $table = "ma_angebot";
    $formId = "66";
    $elementsDic = $elementsDicMa;
  }
}


$data = [];
$oldData = $_POST["getOldData"];
$queryNewTheses = "SELECT `id`,`dateOfSubmission`,`data`,`files` FROM `forms`.`submissions` WHERE `formId`='".$formId."' ORDER BY `dateOfSubmission` DESC LIMIT ".$_POST["limit"][0].",".$_POST["limit"][1];
$offset = $_POST["offset"];
$num = 0;
if(!$oldData) {
  if($result = $connection->query($queryNewTheses)) {
    if($result->num_rows < $_POST["limit"][1]-$_POST["limit"][0]) {
      $offset = $result->num_rows;
      $num = $offset;
      $oldData = true;
    }
    while($row = $result->fetch_assoc()) {
      $displayData = array();
      $notDisplayData = array();

      $submissionData = json_decode($row["data"], true);
      $submissionFiles = json_decode($row["files"], true);
      $displayData["Ansp_Email"] = $submissionData[$elementsDic["Ansp_Email"]];
      $displayData["Ansp_Name"] = $submissionData[$elementsDic["Ansp_Name"]];
      $displayData["VAThema"] = array("title" => $submissionData[$elementsDic["VAThema"]], "file" => $submissionFiles[$elementsDic["XInfo"]]);    
      $displayData["Inst"] = array_slice($instDict, $submissionData[$elementsDic["Inst"]], 1)[0];
      $displayData["DatumX"] = date("d.m.y", strtotime($row["dateOfSubmission"]));
      $notDisplayData["oldData"] = false;
      $notDisplayData["id"] = $row["id"];
      
  

      array_push($data, array("displayData" => $displayData, "notDisplayData" => $notDisplayData));
    }
  }
}






if($oldData) {
  $amount = $_POST["limit"][1] - $num;
  $start = $_POST["limit"][0] - $offset + $num;
  $query = "SELECT `UsNr`, `UsNr_s`,`Ansp_Email`,`Ansp_Name`,date(`DatumX`),`VAThema`,`XInfo` FROM `".$dbName."`.`".$table."` ORDER BY `DatumX` DESC LIMIT ".$start.",".$amount;
  if($result = $connection->query($query)) {
    while($row = $result->fetch_assoc()) {
      $displayData = array();
      $notDisplayData = array();
      $displayData["Ansp_Email"] = $row["Ansp_Email"];
      $displayData["Ansp_Name"] = $row["Ansp_Name"];
      $displayData["VAThema"] = array("title" => $row["VAThema"], "file" => $table."/".$row["XInfo"]);
      $displayData["Inst"] = $instDict[$row["UsNr"]];
      $displayData["DatumX"] = date("d.m.y", strtotime($row["date(`DatumX`)"]));
      $notDisplayData["oldData"] = true;
      $notDisplayData["UsNr_s"] = $row["UsNr_s"];
      $notDisplayData["UsNr"] = $row["UsNr"];

      array_push($data, array("displayData" => $displayData, "notDisplayData" => $notDisplayData));
    }
  }
}


echo json_encode(array(
  "data" => $data,
  "offset" => $offset,
  "oldData" => $oldData
));
?>