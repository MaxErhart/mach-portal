<?php
namespace PdfTemplates\NichtBestanden;


use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;


// function create($geschlecht='', $name='', $email='', $lastname='', $exam_date='', $exam_time='', $vorsitz='', $zeichen='', $date='', $bewerbungs_nummer='', $degree='') {
function create($bewerber=NULL, $lang='de') {

    // GESCHLECHT
    $geschlecht = $bewerber->getAttribute('Geschlecht');
    if($geschlecht=="M" && $lang=='de') {
        $anrede = "Herr";
    } else if($geschlecht=="F" && $lang=='de') {
        $anrede = "Frau";
    } else if($geschlecht=="M" && $lang=='en') {
        $anrede = "Mr";
    }  else if($geschlecht=="F" && $lang=='en') {
        $anrede = "Ms";
    }  else {
        $anrede = "";
    }

    // NAME
    $firstname = $bewerber->Vorname;
    $lastname = $bewerber->Name;
    $name = "{$firstname} {$lastname}";
    if($firstname=="'-") {
        $name = $lastname;
    } else if($lastname=="'-") {
        $name = $firstname;
        $lastname = $firstname;
    }

    // GEBOREN
    $geboren = $bewerber->Geboren->format('d.m.Y');

    // EMAIL
    $email = $bewerber->getAttribute('KIT-E-Mail');

    // EXAM DATE
    $exam_date = $bewerber->entrance_exam->exam_date->format('d.m.Y');

    // EXAM TIME
    $exam_time = $bewerber->entrance_exam->exam_time->format('H:i');

    $legal = $bewerber->entrance_exam->legal;


    // VORSITZ
    $vorsitz = $bewerber->entrance_exam->vorsitz;

    // ZEICHEN
    $zeichen = $bewerber->entrance_exam->zeichen;
    
    // TODAY
    $date = date('d.m.Y');
    
    // BEWERBUNGS NUMMER
    $bewerbungs_nummer = $bewerber->getAttribute('Bewerbungs-nummer');
    
    $entrance_exam_registration_changed = $bewerber->entrance_exam_registration_changed->format('d.m.Y');

    // DEGREE
    if($lang=='de') {
        $degree = "Masterstudiengang {$bewerber->entrance_exam->degree_de}";
    } else if($lang=='en') {
        $degree = "Master's program in {$bewerber->entrance_exam->degree_en}";
    }


    // CREATE PDF
    if(str_starts_with($zeichen, "WS")) {
        $next_exam_month = "März";
      } else {
        $next_exam_month = "September";
      }
      $next_exam_year = substr($zeichen, -2);
      $next_exam = "{$next_exam_month} 20{$next_exam_year}";
      
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
      $pdf->MultiCell(487.5, 17.3, utf8_decode(
"Sie haben am {$exam_date} an der Aufnahmeprüfung für den {$degree} gemäß Satzung für den Zugang zu dem {$degree} am Karlsruher Institut für Technologie (KIT) ($legal) teilgenommen.

Sie haben diese Prüfung leider nicht bestanden, da die erforderliche Mindestpunktzahl gemäß der oben angegebene Zugangssatzung (Anlage 1, Pkt. 5.1) nicht erreicht wurde.

Bitte beachten Sie, dass Sie laut Zugangssatzung zwar nur maximal zweimal an der Aufnahmeprüfung teilnehmen dürfen, pandemiebedingt aber diese Aufnahmeprüfung als nicht teilgenommen gewertet wird. 

Die nächste Aufnahmeprüfung für den Masterstudiengang Maschinenbau wird voraussichtlich {$next_exam} angeboten.

Mit freundlichen Grüßen
KIT-Fakultät für Maschinenbau*"
    ), 0, 'J', 0);
      

      
      
      // BODY LEGAL
      $pdf->SetFont('Helvetica','',10);
      $pdf->SetXY(65.08, 686.6); // set the position of the box
      $pdf->Cell(0, 0, utf8_decode('Rechtsbehelfsbelehrung:'), 0, 0, "L", 1); // add the text, align to Center of cell
      
      $pdf->SetXY(65.08, 695); // set the position of the box
      $pdf->MultiCell(487.5, 17.3, utf8_decode('Gegen diesen Bescheid kann innerhalb eines Monats nach dessen Bekanntgabe schriftlich oder zur Niederschrift beim Präsidium des Karlsruher Instituts für Technologie (KIT) Widerspruch eingelegt werden.'), 0, "J", 0); // add the text, align to Center of cell
      
      $pdf->SetXY(65.08, 750.2); // set the position of the box
      $pdf->Cell(0, 0, utf8_decode('*Dieser Bescheid wurde maschinell erstellt und ist ohne Unterschrift gültig.'), 0, 0, "L", 1); // add the text, align to Center of cell
         




    // render PDF to browser
    $uniqid = uniqid();
    $today = date("d_m_Y");    
    $file_pdf = "D:\inetpub\MPortal\dfiles\bescheide\\{$uniqid}_{$today}.pdf";
    $pdf->Output('F', $file_pdf);
    $base_path = "D:\inetpub\MPortal\dfiles\bescheide";
    $filename = "{$uniqid}_{$today}.pdf";
    return [$file_pdf, $base_path, $filename];
}



?>