<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=UTF-8');

use \setasign\Fpdi\Fpdi;
require_once 'D:\inetpub\MPortal\pdf\fpdf\fpdf.php';
require_once 'D:\inetpub\MPortal\pdf\fpdi\autoload.php';
require_once 'D:\inetpub\MPortal\pdf\Pdf2text\Pdf2text.php';
// require_once 'D:\inetpub\MPortal\pdf\custom_fonts\calibri.php';

$anrede = "";
$name = "asdf";
$email = "asdf@sdf.de";
$bewerbungs_nummer = "12346";
$geboren = "09.09.2022";
$degree = "Masterstudiengang Maschinenbau";
$vorsitz= "5tsfsd";
$zeichen= "WS-22/23";
$date = "12.09.2022";
$exam_date = "15.09.2022";
$legal = "sdfds";

if(str_starts_with($zeichen, "WS")) {
  $next_exam_month = "März";
} else {
  $next_exam_month = "September";
}
$next_exam_year = substr($zeichen, -2);
$next_exam = "{$next_exam_month} {$next_exam_year}";

$template = 'D:\inetpub\MPortal\pdf_templates\bescheide\Lehrv_F2 - Prof.pdf';
// $pdf = new Fpdi('P', 'pt', 'A4');
$pdf = new Fpdi();
$pagecount = $pdf->setSourceFile($template);
$tplIdx = $pdf->importPage(1);
$pdf->AddPage("L");
$pdf->useTemplate($tplIdx);
// $pdf->SetFont('Helvetica');
$pdf->SetFont('Arial');
$pdf->SetFillColor(255, 255, 255);


// Institute 
$pdf->SetXY(20.1, 20.1);
$pdf->SetFontSize('12');
// $pdf->SetTextColor(255,0,0);
$pdf->Cell(80, 8, utf8_decode('Institut für'), 1, 0, "L", 1);

// Title
$pdf->SetXY(80, 32.1);
$pdf->SetFontSize('16');
$pdf->SetFont('Arial', 'b');
$pdf->Cell(140, 8, utf8_decode('Erfüllung der Lehrverpflichtungen für'), 1, 0, "L", 1);

// Semester
$pdf->SetXY(245.3, 20.1);
$pdf->SetFontSize('16');
$pdf->SetFont('Arial', 'b');
$pdf->Cell(40, 8, utf8_decode('WS'), 1, 0, "L", 1);


// Table rows
function create_row($y, $pdf) {
  $pdf->SetXY(20.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(35.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(46.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(125.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(190.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(203.1, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(220.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(235.8, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(249.1, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);

  $pdf->SetXY(264.1, $y);
  $pdf->SetFontSize('12');
  $pdf->SetFont('Arial');
  $pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);
}

create_row(90.1, $pdf);
create_row(95.6, $pdf);
create_row(101.1, $pdf);
create_row(106.6, $pdf);
create_row(106.6, $pdf);
create_row(112.1, $pdf);
create_row(117.6, $pdf);
create_row(123.1, $pdf);
create_row(128.6, $pdf);
create_row(134.1, $pdf);
create_row(139.6, $pdf);
create_row(145.1, $pdf);
create_row(150.6, $pdf);
create_row(156.1, $pdf);

$pdf->SetXY(235.8, 168);
$pdf->SetFontSize('12');
$pdf->SetFont('Arial');
$pdf->Cell(15, 8, utf8_decode('Blank'), 0, 0, "L", 0);


$pdf->Output();
?>