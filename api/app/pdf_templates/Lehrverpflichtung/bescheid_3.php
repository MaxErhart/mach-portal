<?php
namespace Lehrverpflichtung\Bescheid_3;


use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;


// function create($submissions_group,$file,$elements,$submissions,$inst,$prof,$semester) {
function create($submissions_group,$file,$prof,$inst,$semester,$ml_1=null,$ml_2=null,$compound_form_data=null) {
  // dd([$ml_1,$ml_2]);
  $pdf = new Fpdi('P', 'mm', 'A4');
  $pdf->SetFont('Arial');
  $pdf->setDocumentFormattingSize(12);

  if($ml_1) {
    $pagecount = $pdf->setSourceFile($ml_1);
    for($count = 1; $count<$pagecount+1;$count++) {
      $pdf->AddPage();
      $tplIdx = $pdf->importPage($count);
      $pdf->useTemplate($tplIdx);
    }
  }

  if($ml_2) {
    $pagecount = $pdf->setSourceFile($ml_2);
    for($count = 1; $count<$pagecount+1;$count++) {
      $pdf->AddPage();
      $tplIdx = $pdf->importPage($count);
      $pdf->useTemplate($tplIdx);
    }
  }


  $pdf->AddPage();


  $settings = [
    "padding_x"=>20,
    "padding_y"=>20,
    "width"=>170,
    "height"=>257,
    "text_padding"=>1,
  ];

  $pdf->setDocumentSettings($settings);

  $pdf->SetFontSize('16');
  $pdf->bold();
  $pdf->SetXY($settings["padding_x"],$settings["padding_y"]);
  $pdf->MultiCell($settings["width"]-30, $pdf->fontHeight(), utf8_decode($inst), 0,"L",0);


  $pdf->SetXY($settings["padding_x"],$settings["padding_y"]);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(), utf8_decode($semester), 0,"R",0);

  $y = $pdf->padding(8);
  $pdf->SetFontSize('14');
  $pdf->SetXY($settings["padding_x"], $y);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(14), utf8_decode("{$prof}"), 0,"L",0);

  $y = $pdf->padding(10);
  $pdf->SetFontSize('12');
  $pdf->SetXY($settings["padding_x"],$y);
  $pdf->MultiCell($settings["width"], $pdf->fontHeight(), utf8_decode('C. Erklärung zur Erfüllung der Lehrverpflichtung'), 0,"L",0);

  // prof 984
  //  987

  $pdf->bold(false);
  $y = $pdf->padding(4);
  $pdf->SetXY(20,$y+10-10);
  $pdf->SetFont('Arial', '', '12');
  $pdf->Cell(170,5,'a) Die Lehrverpflichtungen der Professoren des Instituts');

  $ticked_box_prof = [false,false,false];
  $checkbox_1_id = $compound_form_data["checkbox_1"];
  $checkbox_2_id = $compound_form_data["checkbox_2"];
  if($submissions_group[0]->_data[$checkbox_1_id]==1) {
    $ticked_box_prof = [true,false,false];
  } else if($submissions_group[0]->_data[$checkbox_1_id]==2) {
    $ticked_box_prof = [false,true,false];

  } else if($submissions_group[0]->_data[$checkbox_1_id]==3) {
    $ticked_box_prof = [false,false,true];
  }

  $ticked_box_mitarb = [false,false,false];
  if($submissions_group[0]->_data[$checkbox_2_id]==0) {
    $ticked_box_mitarb = [true,false,false];
  } else if($submissions_group[0]->_data[$checkbox_2_id]==1) {
    $ticked_box_mitarb = [false,true,false];

  } else if($submissions_group[0]->_data[$checkbox_2_id]==2) {
    $ticked_box_mitarb = [false,false,true];
  }


  $pdf->SetXY(20,$y+17-10);
  $pdf->tabIndent();
  $pdf->checkbox(3,$ticked_box_prof[0]);
  $pdf->spaceIndent();
  $pdf->Cell(150,3,utf8_decode('wurden im vergangenen Semester erfüllt'));

  $pdf->SetXY(20,$y+23-10);
  $pdf->tabIndent();
  $pdf->checkbox(3,$ticked_box_prof[1]);
  $pdf->spaceIndent();
  $pdf->Cell(150,3,utf8_decode('wurden / werden innerhalb von einem / zwei Studienjahr(en) ausgeglichen'));

  $pdf->SetXY(20,$y+29-10);
  $pdf->tabIndent();
  $pdf->checkbox(3,$ticked_box_prof[2]);
  $pdf->spaceIndent();
  $pdf->Cell(150,3,utf8_decode('wurden nicht erfüllt (Begründung siehe Anlage)'));

  $pdf->SetXY(20,$y+37-10);
  $pdf->Cell(170,5, utf8_decode('b) Die Lehrverpflichtungen der weiteren Lehrpersonen des Instituts'));


  $pdf->SetXY(20,$y+44-10);
  $pdf->tabIndent();
  $pdf->checkbox(3,$ticked_box_mitarb[0]);
  $pdf->spaceIndent();
  $pdf->Cell(150,3,utf8_decode('wurden im vergangenen Semester erfüllt'));

  $pdf->SetXY(20,$y+50-10);
  $pdf->tabIndent();
  $pdf->checkbox(3,$ticked_box_mitarb[1]);
  $pdf->spaceIndent();
  $pdf->Cell(150,3,utf8_decode('wurden / werden innerhalb von einem / zwei Studienjahr(en) ausgeglichen'));

  $pdf->SetXY(20,$y+56-10);
  $pdf->tabIndent();
  $pdf->checkbox(3,$ticked_box_mitarb[2]);
  $pdf->spaceIndent();
  $pdf->Cell(150,3,utf8_decode('wurden nicht erfüllt (Begründung siehe Anlage)'));

  $pdf->SetXY(20,267);
  $pdf->Cell(80,5,utf8_decode('Karlsruhe,_____________________'));

  $pdf->SetXY(130,267);
  $pdf->Cell(60,5,utf8_decode('_____________________'),0,0,'R',0);

  $pdf->SetXY(130,272);
  $pdf->Cell(60,5,utf8_decode('Institutsleiter'),0,0,'R',0);


  // $pdf->SetXY($settings["padding_x"],$y);
  // $pdf->MultiCell($settings["width"], $pdf->fontHeight(), utf8_decode('Für das wissenschaftliche Personal des Instituts bestehen gemäß LVVO bzw. Funktionsbeschreibungen / Dienstverträgen die folgenden Lehrverpflichtungen in SWh:'), 0,"L",0);

  $pdf->Output('F', $file);
  
  return $file;
}
?>