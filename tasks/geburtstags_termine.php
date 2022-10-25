<?php
error_reporting(E_ALL);
echo "<pre>";
$test=false;
if (isset($tx))$test=true;
ini_set("display_errors", 1);
$h_txt="Admin - Termine";
$ret="Admin.php";
$Prog="NO";
$err=false;
$tab="";
$from="From: portal@mach.kit.edu";
$to_s="ute.rietschel@kit.edu";

if (isset($argv[1])){
	$tb=$argv[1];
} else {
	$err=true;
}
if (!$err){
	if ($tb==1)$tab="adm_termine_dek";
	if ($tb==2)$tab="adm_termine_priv";
	if ($tb==3)$tab="adm_termine_fak";
}
if ($tab=="")$err=true;
if ($err){
	$err_txt="Falscher Aufruf";
	if ($test)	$err_txt.=" (TEST)";
	mb_send_mail ($to_s,"!!!! Fehler adm_termine!",$err_txt,$from);
	exit;
}
include_once "fsql_inc.php";
include_once "share_inc.php";
db_anmelde3("FamXExec","user");
function get_day_info($art,$dx0,$dt0,$dt){
   $d_sec=86400;
   $dx= (fsd_get_d ($dt,1));
   $dt1=mktime(0, 0, 0, $dx[1], $dx[0], $dx0[2]);
   $dt2=mktime(0, 0, 0, $dx[1], $dx[0], $dx0[2]+1);
   if (($dt1-$dt0)>=0){
	  $j=$dx0[2]-$dx[2];
	  $t=$dt1/$d_sec-$dt0/$d_sec;
  } else {
	  $j=$dx0[2]-$dx[2]+1;
	  $t=$dt2/$d_sec-$dt0/$d_sec;
  }
  if ($art==1){
	  return $t;
  } else {
	  return $j;
  }
}
$p=($tb==2);
$f=($tb==3);
$result = fsql_query ("select * from ".$tab);
date_default_timezone_set('UTC');
$heute=fsd_get_dbf();
$dx0= (fsd_get_d ($heute,1));
$dt0=mktime(0, 0,0, $dx0[1], $dx0[0], $dx0[2]);
$ev_ar=array (7,1,0);
$to_ar=array("ute.rietschel@kit.edu","anna.ginder@kit.edu","ruth.beckmann@kit.edu","carsten.proppe@kit.edu");
$x_txt="Dekanat";
if ($p){
	$to_ar=array("kurt.sutter@hksu.de","heike.sutter@hksu.de");
    $x_txt="Privat";
}
if ($f){
    $to_ar=array("ute.rietschel@kit.edu","ruth.beckmann@kit.edu","anna.ginder@kit.edu");
    $ev_ar=array (28,7,1,0);
    $x_txt=mb_convert_encoding("Fakultät","ISO-8859-1","UTF-8");
}
if ($test){
	$to_ar=array("ute.rietschel@kit.edu");
	$x_txt="**TEST**/".$x_txt;
}
// $to_ar=array("ute.rietschel@kit.edu", "dr4842@partner.kit.edu");
while ($rx=fsql_fetch_assoc($result)){
   $tage=get_day_info(1,$dx0,$dt0,$rx["GebDat"]);
   $alter=get_day_info(2,$dx0,$dt0,$rx["GebDat"]);
   $ev=$rx["TEvent"];
   $send = false;


   foreach ($ev_ar as $i=>$ev_tag){
	if ($tage<=$ev_tag){
		$send = true;
		$i_mrk = $i + 1;
    }
   }
	  if ($send){
		  $subj="*** ".$i_mrk.". Geburtstagsmeldung (".$x_txt.") ".$rx["VName"]." ".$rx["Name"]." ***";
		  if ($tage==0){
    		  $xtxt="heute";
		  } else if ($tage==1){
    		  $xtxt="morgen";
		  }	  else {
    		  $xtxt="in ".$tage." Tagen";
		  }
		  $stxt="";
		  if ($f)$stxt=" (".$rx["Status"].")";
		  if ($p||$f)$xtxt.=" den ".$alter;
		  $text="Dies ist eine automatisch erzeugte Mitteilung.\n\n".$rx["VName"]." ".$rx["Name"].$stxt." hat ".$xtxt." Geburtstag.";
		  foreach ($to_ar as $to){
		      mb_send_mail ($to,$subj,$text,$from);
		  }
	 }
}
?>
