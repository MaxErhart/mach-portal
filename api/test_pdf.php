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

$template = 'D:\inetpub\MPortal\pdf_templates\bescheide\Bescheid_Maske_ohne.pdf';
$pdf = new Fpdi('P', 'pt', 'A4');
$pagecount = $pdf->setSourceFile($template);
$tplIdx = $pdf->importPage(1);
$pdf->AddPage();
$pdf->useTemplate($tplIdx);
$pdf->SetFont('Helvetica');
$pdf->SetFillColor(255, 255, 255);

// APPLICANT INFO BOX
$pdf->SetXY(64, 159); // set the position of the box
$pdf->Cell(280, 80, '', 0, 0, "L", 1); // add the text, align to Center of cell
$pdf->SetFontSize('10'); // set font size

// ANREDE
$pdf->SetXY(65.08, 165); // set the position of the box
$pdf->Cell(0, 0, utf8_decode($anrede), 0, 0, "L", 1); // add the text, align to Center of cell
//  NAME
$pdf->SetXY(65.08, 178); // set the position of the box
$pdf->Cell(0, 0, utf8_decode($name), 0, 0, "L", 1); // add the text, align to Center of cell
//  EMAIL
$pdf->SetXY(65.08, 191); // set the position of the box
$pdf->Cell(0, 0, utf8_decode($email), 0, 0, "L", 1); // add the text, align to Center of cell
// APPLICATION NUMBER
$pdf->SetXY(65.08, 204); // set the position of the box
$pdf->Cell(0, 0, "Bewerbungs-Nummer: {$bewerbungs_nummer}", 0, 0, "L", 1); // add the text, align to Center of cell

// APPLICATION NUMBER
$pdf->SetXY(65.08, 217); // set the position of the box
$pdf->Cell(0, 0, "Geboren: {$geboren}", 0, 0, "L", 1); // add the text, align to Center of cell




// HIDE TOP RIGHT
$pdf->SetXY(374, 89); // set the position of the box
$pdf->Cell(150, 135, '', 0, 0, "L", 1); // add the text, align to Center of cell

// TOP RIGHT FAK
// TOP RIGHT FAK
$pdf->SetFont('Helvetica','B',8);
// $pdf->SetXY(375.2, 95); // set the position of the box
$pdf->SetXY(375.2, 83.8); // set the position of the box
$pdf->MultiCell(177.5, 9.7, utf8_decode("KIT-Fakultät für Maschinenbau Zugangskommission für den {$degree}"), 0, "L", 0);

// TOP RIGHT VORSITZ
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 127.7); // set the position of the box
$pdf->Cell(0, 0, "Vorsitz:  $vorsitz", 0, 0, "L", 1); // add the text, align to Center of cell

// TOP RIGHT ADRESS
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 144); // set the position of the box
$pdf->Cell(0, 0, 'Kaiserstr. 12', 0, 0, "L", 1); // add the text, align to Center of cell

$pdf->SetXY(375.2, 154); // set the position of the box
$pdf->Cell(0, 0, '76131 Karlsruhe', 0, 0, "L", 1); // add the text, align to Center of cell

// TOP RIGHT EMAIL
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 174); // set the position of the box
$pdf->Cell(0, 0, 'E-Mail:', 0, 0, "L", 1); // add the text, align to Center of cell
$pdf->SetXY(433.7, 174); // set the position of the box
$pdf->Cell(0, 0, 'zk-aprf@mach.kit.edu', 0, 0, "L", 1); // add the text, align to Center of cell

// TOP RIGHT WEB
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 184); // set the position of the box
$pdf->Cell(0, 0, 'Web:', 0, 0, "L", 1); // add the text, align to Center of cell
$pdf->SetXY(433.7, 184); // set the position of the box
$pdf->Cell(0, 0, 'www.mach.kit.edu', 0, 0, "L", 1); // add the text, align to Center of cell


// TOP RIGHT BEARBEITER
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 200); // set the position of the box
$pdf->Cell(0, 0, 'Bearbeiter:', 0, 0, "L", 1); // add the text, align to Center of cell
$pdf->SetXY(433.7, 200); // set the position of the box
$pdf->Cell(0, 0, 'Dipl.-Ing. Ute Rietschel', 0, 0, "L", 1); // add the text, align to Center of cell


// // TOP RIGHT ZEICHEN
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 225.7); // set the position of the box
$pdf->Cell(0, 0, 'Unser Zeichen:', 0, 0, "L", 1); // add the text, align to Center of cell
$pdf->SetXY(433.7, 225.7); // set the position of the box
$pdf->Cell(0, 0, "{$zeichen}_{$bewerbungs_nummer}", 0, 0, "L", 1); // add the text, align to Center of cell

// // TOP RIGHT DATUM
$pdf->SetFont('Helvetica','',8);
$pdf->SetXY(375.2, 235.7); // set the position of the box
$pdf->Cell(0, 0, 'Datum:', 0, 0, "L", 1); // add the text, align to Center of cell
$pdf->SetXY(433.7, 235.7); // set the position of the box
$pdf->Cell(0, 0, $date, 0, 0, "L", 1); // add the text, align to Center of cell





// TITLE BOX
$pdf->SetXY(60, 254); // set the position of the box
$pdf->Cell(488, 52, '', 0, 0, "L", 1); // add the text, align to Center of cell

// TITLE
$pdf->SetFont('Helvetica','B',10);
$pdf->SetXY(65.08, 326.6); // set the position of the box
$pdf->Cell(0, 0, utf8_decode("Aufnahmeprüfung für den {$degree}"), 0, 0, "L", 1); // add the text, align to Center of cell

// BODY ANREDE
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(65.08, 352); // set the position of the box
$pdf->Cell(0, 0, 'Sehr geehrte Dame, sehr geehrter Herr,', 0, 0, "L", 1); // add the text, align to Center of cell

// BODY TEXT 1
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(65.08, 372.5); // set the position of the box
$pdf->MultiCell(487.5, 17.3, utf8_decode("Sie haben am {$exam_date} an der Aufnahmeprüfung für den {$degree} gemäß Satzung für den Zugang zu dem {$degree} am Karlsruher Institut für Technologie (KIT) ($legal) teilgenommen."), 0, 'J', 0);

// BODY TEXT 2
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(65.08, 437.5); // set the position of the box
$pdf->MultiCell(487.5, 17.3, utf8_decode("Sie haben diese Prüfung leider nicht bestanden, da die erforderliche Mindestpunktzahl gemäß der o.a. Zugangssatzung (Anlage 1, Pkt. 5.1) nicht erreicht wurde."), 0, "J", 1); // add the text, align to Center of cell

// BODY TEXT 3
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(65.08, 486.5); // set the position of the box
$pdf->MultiCell(487.5, 17.3, utf8_decode("Bitte beachten Sie, dass Sie laut Zugangssatzung maximal zweimal an der Aufnahmeprüfung teilnehmen dürfen."), 0, "J", 1); // add the text, align to Center of cell

// BODY TEXT 3
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(65.08, 532.5); // set the position of the box
$pdf->MultiCell(487.5, 17.3, utf8_decode("Die nächste Aufnahmeprüfung für den Masterstudiengang Maschinenbau wird voraussichtlich {$next_exam} angeboten."), 0, "J", 1); // add the text, align to Center of cell

 
// BODY FIN
$pdf->SetXY(65.08, 590.2); // set the position of the box
$pdf->Cell(0, 0, utf8_decode('Mit freundlichen Grüßen'), 0, 0, "L", 1); // add the text, align to Center of cell

$pdf->SetXY(65.08, 607.2); // set the position of the box
$pdf->Cell(0, 0, utf8_decode('KIT-Fakultät für Maschinenbau*'), 0, 0, "L", 1); // add the text, align to Center of cell

// $pdf->SetXY(65.08, 571.2); // set the position of the box
// $pdf->Cell(0, 0, utf8_decode('Kopie: Studierendenservice, Zulassungskommission'), 0, 0, "L", 1); // add the text, align to Center of cell



// BODY LEGAL
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(65.08, 646.6); // set the position of the box
$pdf->Cell(0, 0, utf8_decode('Rechtsbehelfsbelehrung:'), 0, 0, "L", 1); // add the text, align to Center of cell

$pdf->SetXY(65.08, 655); // set the position of the box
$pdf->MultiCell(487.5, 17.3, utf8_decode('Gegen diesen Bescheid kann innerhalb eines Monats nach dessen Bekanntgabe schriftlich oder zur Niederschrift beim Präsidium des Karlsruher Instituts für Technologie (KIT) Widerspruch eingelegt werden.'), 0, "J", 0); // add the text, align to Center of cell

$pdf->SetXY(65.08, 710.2); // set the position of the box
$pdf->Cell(0, 0, utf8_decode('*Dieser Bescheid wurde maschinell erstellt und ist ohne Unterschrift gültig.'), 0, 0, "L", 1); // add the text, align to Center of cell


// // render PDF to browser
$pdf->Output();
?>