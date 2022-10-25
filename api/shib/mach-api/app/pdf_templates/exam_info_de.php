<?php
namespace PdfTemplates\ExamInfo;


use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;


function create($bewerber=NULL, $lang='de') {

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


    $degree= utf8_decode("sdfg,");
    $exam_date = "Feb 14, 2022";
    $exam_time = "14.00 pm";
    $exam_duration = "90 minutes";
    $exam_turnin = "16.00 pm";

    // CREATE PDF

    $pdf = new Fpdi('P', 'pt', 'A4');
    $pdf->AddFont('Calibri','','Calibri Regular.php');
    $pdf->AddFont('Calibri','B','Calibri Bold.php');
    $pdf->SetFillColor(255, 255, 255);    

    $template = 'D:\inetpub\MPortal\pdf_templates\bescheide\matwerk_aufnahmepruefung_220914_anschreiben.pdf';

    $pagecount = $pdf->setSourceFile($template);
    
    
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);
    
    $pdf->SetFont('Calibri','',11.05);
    $pdf->SetXY(263.5, 53.5);
    $pdf->Cell(0, 22, $degree, 0, 0, "L", 1);
    
    $pdf->SetFont('Calibri','B',11);
    $pdf->SetXY(399.5, 86.45);
    $pdf->Cell($pdf->GetStringWidth($exam_date), 22, $exam_date, 0, 0, "L", 1);
    $pdf->SetFont('Calibri','',11);
    $pdf->SetXY(399.5+$pdf->GetStringWidth($exam_date), 86.45);
    $pdf->Cell(0, 22, ", in what", 0, 0, "L", 1);
    
    
    
    $pdf->SetFont('Calibri','B',11);
    $x = 321.5;
    $y = 123.5;
    $pdf->SetXY($x, $y);
    $pdf->Cell($pdf->GetStringWidth($exam_duration), 15, $exam_duration, 0, 0, "L", 1);
    $pdf->SetFont('Calibri','',11);
    $pdf->SetXY($x+$pdf->GetStringWidth($exam_duration)+0.7, $y);
    $pdf->Cell(0, 15, ".", 0, 0, "L", 1);
    
    
    $pdf->SetFont('Calibri','B',11);
    $x = 194;
    $y = 137.5;
    $pdf->SetXY($x, $y);
    $pdf->Cell($pdf->GetStringWidth($exam_time), 20, $exam_time, 0, 0, "L", 1);
    $pdf->SetFont('Calibri','',11);
    $pdf->SetXY($x+$pdf->GetStringWidth($exam_time), $y);
    $pdf->Cell(0, 20, " (CET, UTC/GMT+1).", 0, 0, "L", 1);
    
    
    
    
    
    $pdf->SetFont('Calibri','B',11);
    $x = 247;
    $y = 282;
    $pdf->SetXY($x, $y);
    $pdf->Cell($pdf->GetStringWidth($exam_turnin), 15, $exam_turnin, 0, 0, "L", 1);
    $pdf->SetFont('Calibri','',11);
    $pdf->SetXY($x+$pdf->GetStringWidth($exam_turnin), $y);
    $pdf->Cell(0, 15, " (CET, UTC/GMT+1) by submission of answers and", 0, 0, "L", 1);
    
    
    
    
    $pdf->AddPage();
    $tplIdx = $pdf->importPage(2);
    $pdf->useTemplate($tplIdx);    




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