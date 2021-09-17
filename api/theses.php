<?php
include_once("D:\inetpub\MPortal\includes\dbFramework\main.php");
include_once("D:\inetpub\MPortal\includes\userFramework\main.php");
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$serverName = "localhost";
$dbName = "mach_portal";
$user = "mach-portal";
$dbPassword = "motor25";
$connection = new mysqli($serverName, $user, $dbPassword, "user");
$dbSchema = new dbSchema($serverName, $user, $dbPassword, $dbName);
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
  "Ansp_Email" => "11145253",
  "Ansp_Name" => "90460037",
  "VAThema" => "53165428",
  "Inst" => "44193425",
  "XInfo" => "82199106"
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
$offset = $_POST["offset"];
$num = 0;

// check if read/write ids for theses have already been fetched if not save fetched ids in session
if($_SESSION["user"]["rights"]["theses"]) {
  if(!array_key_exists("ids", $_SESSION["user"]["rights"]["theses"])){
    $ids = $dbSchema->getUserIds($_SESSION["user"]["rights"]["theses"]);
    $_SESSION["user"]["rights"]["theses"]["ids"]=$ids;
  } else {
    $ids = $_SESSION["user"]["rights"]["theses"]["ids"];
  }
}



if(!$oldData) {

  if($ids["read"] == "all") {
    $colTypePairs = array(
      "data" => "JSON",
      "files" => "JSON",
      "dateOfSubmission" => "date"
    );
    $conditions = array(
      "formId" => $formId
    );    
    $theses = $dbSchema->selectTable("form_submissions")->select("formSubmissionId", "userId", "dateOfSubmission", "data", "files")->conditions($conditions)->orderBy("dateOfSubmission", "DESC")->get($_POST["limit"][1], $_POST["limit"][0], $colTypePairs);

    if(count($theses) < $_POST["limit"][1]-$_POST["limit"][0]) {
      $offset = count($theses);
      $num = $offset;
      $oldData = true;    
    }
    // print_r($theses);
    for($i=0;$i<count($theses); $i++) {
      $displayData["Ansp_Email"] = $theses[$i]["data"][$elementsDic["Ansp_Email"]];
      $displayData["Ansp_Name"] = $theses[$i]["data"][$elementsDic["Ansp_Name"]];
      $displayData["VAThema"] = array("title" => $theses[$i]["data"][$elementsDic["VAThema"]], "file" => $theses[$i]["files"][$elementsDic["XInfo"]]);    
      $displayData["Inst"] = array_slice($instDict, $theses[$i]["data"][$elementsDic["Inst"]], 1)[0];
      $displayData["DatumX"] = $theses[$i]["dateOfSubmission"];
      $notDisplayData["oldData"] = false;
      $notDisplayData["id"] = $theses[$i]["formSubmissionId"];
      if($ids["write"] == "all") {
       $notDisplayData["write"] = true;
      } else if(in_array($theses[$i]["userId"], $ids["write"])) {
        $notDisplayData["write"] = true;
      } else {
        $notDisplayData["write"] = false;
      }
      array_push($data, array("displayData" => $displayData, "notDisplayData" => $notDisplayData));
    }    
  } else {
    $conditions = array(
      "formId" => $formId,
      "userId" => $ids["read"]
    );
    $colTypePairs = array(
      "data" => "JSON",
      "files" => "JSON",
      "dateOfSubmission" => "date"
    );    
    $theses = $dbSchema->selectTable("form_submissions")->select("formSubmissionId", "userId", "dateOfSubmission", "data", "files")->conditions($conditions)->orderBy("dateOfSubmission", "DESC")->get($_POST["limit"][1], $_POST["limit"][0], $colTypePairs);
    if(count($theses) < $_POST["limit"][1]-$_POST["limit"][0]) {
      $offset = count($theses);
      $num = $offset;
      $oldData = true;    
    }
    for($i=0;$i<count($theses); $i++) {
      $displayData["Ansp_Email"] = $theses[$i]["data"][$elementsDic["Ansp_Email"]];
      $displayData["Ansp_Name"] = $theses[$i]["data"][$elementsDic["Ansp_Name"]];
      $displayData["VAThema"] = array("title" => $theses[$i]["data"][$elementsDic["VAThema"]], "file" => $theses[$i]["files"][$elementsDic["XInfo"]]);    
      $displayData["Inst"] = array_slice($instDict, $theses[$i]["data"][$elementsDic["Inst"]], 1)[0];
      $displayData["DatumX"] = $theses[$i]["dateOfSubmission"];
      $notDisplayData["oldData"] = false;
      $notDisplayData["id"] = $theses[$i]["formSubmissionId"];
      if(in_array($theses[$i]["userId"], $ids["write"])) {
        $notDisplayData["write"] = true;
      } else {
        $notDisplayData["write"] = false;
      }
      array_push($data, array("displayData" => $displayData, "notDisplayData" => $notDisplayData));
    } 

    
  }




}






if($oldData) {
  $amount = $_POST["limit"][1] - $num;
  $start = $_POST["limit"][0] - $offset + $num;

  $query = "SELECT `UsNr`, `UsNr_s`,`Ansp_Email`,`Ansp_Name`,date(`DatumX`),`VAThema`,`XInfo` FROM `user`.`".$table."` ORDER BY `DatumX` DESC LIMIT ".$start.",".$amount;
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
      if($ids["write"] == "all") {
        $notDisplayData["write"] = true;
      } else {
        $notDisplayData["write"] = false;
      }

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