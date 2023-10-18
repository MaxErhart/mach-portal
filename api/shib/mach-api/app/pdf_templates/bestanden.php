<?php
namespace PdfTemplates\Bestanden;


use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;
use Illuminate\Support\Facades\Storage;


// function create($geschlecht='', $name='', $email='', $lastname='', $exam_date='', $exam_time='', $vorsitz='', $zeichen='', $date='', $bewerbungs_nummer='', $degree='') {
function create($bewerber=NULL, $lang='de') {



    // NAME
    $firstname = $bewerber->getAttribute("First name");
    $lastname = $bewerber->getAttribute("Last name");
    $name = "{$firstname} {$lastname}";
    if($firstname=="'-") {
        $name = $lastname;
    } else if($lastname=="'-") {
        $name = $firstname;
        $lastname = $firstname;
    }

    // GESCHLECHT
    $Anrede = $bewerber->getAttribute('Form of address');
    if($Anrede=="Herr" && $lang=='de') {
        $anrede = "Herr";
        $anrede_body = "Sehr geehrter Herr {$name},";
    } else if($Anrede=="Frau" && $lang=='de') {
        $anrede = "Frau";
        $anrede_body = "Sehr geehrte Frau {$name},";
    } else if($Anrede=="Herr" && $lang=='en') {
        $anrede = "Mr";
        $anrede_body = "Dear Mr {$name},";
    }  else if($Anrede=="Frau" && $lang=='en') {
        $anrede = "Ms";
        $anrede_body = "Dear Ms {$name},";
    }  else {
        $anrede = "";
        $anrede_body = "Sehr geehrte Dame, sehr geehrter Herr,";
    }

    // GEBOREN
    $geboren = $bewerber->getAttribute('Date of birth')->format('d.m.Y');

    // EMAIL
    $email = $bewerber->getAttribute('Email');


    // EXAM DATE
    $exam_date = $bewerber->entrance_exam->exam_date->format('d.m.Y');

    // EXAM TIME
    $exam_time = $bewerber->entrance_exam->exam_time;

    $legal = $bewerber->entrance_exam->legal;


    // VORSITZ
    $vorsitz = $bewerber->entrance_exam->vorsitz;

    // ZEICHEN
    $zeichen = $bewerber->entrance_exam->zeichen;
    
    // TODAY
    $date = date('d.m.Y');
    
    // BEWERBUNGS NUMMER
    $bewerbungs_nummer = $bewerber->getAttribute('Number');
    $bewerbungs_nummer = str_replace(",","",$bewerbungs_nummer);
    $bewerbungs_nummer = str_replace(".","",$bewerbungs_nummer);
    
    $entrance_exam_registration_changed = $bewerber->entrance_exam_registration_changed->format('d.m.Y');

    // DEGREE
    if($lang=='de') {
        $degree = "Masterstudiengang {$bewerber->entrance_exam->degree_de}";
    } else if($lang=='en') {
        $degree = "Master's program in {$bewerber->entrance_exam->degree_en}";
    }


    // CREATE PDF
    $template = 'D:\inetpub\MPortal\pdf_templates\bescheide\Bescheid_Maske_ohne.pdf';
    $pdf = new Fpdi('P', 'pt', 'A4');
    $pagecount = $pdf->setSourceFile($template);
    $tplIdx = $pdf->importPage(1);
    $pdf->AddPage();
    $pdf->useTemplate($tplIdx);
    $pdf->SetFont('Helvetica');
    $pdf->SetFillColor(255, 255, 255);

    // HIDE 1
    $pdf->SetXY(64, 159); // set the position of the box
    $pdf->Cell(280, 80, '', 0, 0, "L", 1); // add the text, align to Center of cell

    // HIDE 1.1
    $pdf->SetXY(60, 254); // set the position of the box
    $pdf->Cell(488, 52, '', 0, 0, "L", 1); // add the text, align to Center of cell

    // HIDE 3
    $pdf->SetXY(124.5, 345.5); // set the position of the box
    $pdf->Cell(100, 13, '', 0, 0, "L", 1); // add the text, align to Center of cell

    // HIDE TOP RIGHT
    $pdf->SetXY(374, 89); // set the position of the box
    $pdf->Cell(150, 135, '', 0, 0, "L", 1); // add the text, align to Center of cell



    // TEXT 1
    $pdf->SetFontSize('10'); // set font size

    // ANREDE
    $pdf->SetXY(65.08, 165); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($anrede), 0, 0, "L", 1); // add the text, align to Center of cell
    //  NAME
    $pdf->SetXY(65.08, 178); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($name), 0, 0, "L", 1); // add the text, align to Center of cell
    // ADRESS STREET
    $pdf->SetXY(65.08, 191); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($bewerber->getAttribute('Street and street number')), 0, 0, "L", 0); // add the text, align to Center of cell

    // ADRESS TOWN
    $pdf->SetXY(65.08, 204); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($bewerber->getAttribute('ZIP').' '.$bewerber->getAttribute('Town')), 0, 0, "L", 0); // add the text, align to Center of cell

    // ADRESS COUNTRY
    $pdf->SetXY(65.08, 217); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($bewerber->getAttribute('Country')), 0, 0, "L", 1); // add the text, align to Center of cell

    if($lang=='de') {
        $bewerbungs_nummer_indicator = "Bewerbungsnummer";
    } else {
        $bewerbungs_nummer_indicator = "Application Number";
    }

    if($lang=='de') {
        $geboren_indicator = "Geboren";
    } else {
        $geboren_indicator = "Date of birth";
    }


    //  EMAIL
    $pdf->SetXY(65.08, 230); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($email), 0, 0, "L", 1); // add the text, align to Center of cell
    
    // GEBOREN
    $pdf->SetXY(65.08, 243); // set the position of the box
    $pdf->Cell(0, 0, "{$geboren_indicator}: {$geboren}", 0, 0, "L", 1); // add the text, align to Center of cell
    
    // APPLICATION NUMBER
    $pdf->SetXY(65.08, 256); // set the position of the box
    $pdf->Cell(0, 0, "{$bewerbungs_nummer_indicator}: {$bewerbungs_nummer}", 0, 0, "L", 1); // add the text, align to Center of cell


    
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
    
    
    
    
    // TITLE
    $pdf->SetFont('Helvetica','B',10);
    $pdf->SetXY(65.08, 326.6); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode("Aufnahmeprüfung für den {$degree}"), 0, 0, "L", 1); // add the text, align to Center of cell
    
    // BODY ANREDE
    $pdf->SetFont('Helvetica','',10);
    $pdf->SetXY(65.08, 352); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode($anrede_body), 0, 0, "L", 1); // add the text, align to Center of cell
    
    // BODY TEXT 1
    $pdf->SetFont('Helvetica','',10);
    $pdf->SetXY(65.08, 372.5); // set the position of the box
    $pdf->MultiCell(487.5, 17.3, utf8_decode(
"Sie haben am {$exam_date} an der Aufnahmeprüfung für den {$degree} gemäß Satzung für den Zugang zu dem {$degree} am Karlsruher Institut für Technologie (KIT) ($legal) teilgenommen.

Sie haben diese Prüfung bestanden und können zum {$degree} zugelassen werden, sofern keine anderen zulassungsverhindernden Gründe vorliegen.

Mit freundlichen Grüßen
KIT-Fakultät für Maschinenbau*"
    ), 0, 'J', 0);
    
    
    // BODY FIN
    $pdf->SetXY(65.08, 621.2); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode('Kopie: Studierendenservice, Zulassungskommission, International Students Office (IStO)'), 0, 0, "L", 1); // add the text, align to Center of cell
    
    
    
    // BODY LEGAL
    $pdf->SetFont('Helvetica','',10);

    $pdf->SetXY(65.08, 686.6); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode('Rechtsbehelfsbelehrung:'), 0, 0, "L", 1); // add the text, align to Center of cell
    
    $pdf->SetXY(65.08, 703.9); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode('Die Amtliche Bekantmachungen finden Sie unter'), 0, 0, "L", 1); // add the text, align to Center of cell
    $pdf->SetXY(284, 703.9); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode('https://www.sle.kit.edu/amtlicheBekanntmachungen.php'), 0, 0, "L", 1,"https://www.sle.kit.edu/amtlicheBekanntmachungen.php"); // add the text, align to Center of cell


    $pdf->SetXY(65.08, 712.3); // set the position of the box
    $pdf->MultiCell(487.5, 17.3, utf8_decode('Gegen diesen Bescheid kann innerhalb eines Monats nach dessen Bekanntgabe schriftlich oder zur Niederschrift beim Präsidium des Karlsruher Instituts für Technologie (KIT) Widerspruch eingelegt werden.'), 0, "J", 0); // add the text, align to Center of cell
    $y = $pdf->GetY();
    $pdf->SetXY(65.08, $y+17.3); // set the position of the box
    $pdf->Cell(0, 0, utf8_decode('*Dieser Bescheid wurde maschinell erstellt und ist ohne Unterschrift gültig.'), 0, 0, "L", 1); // add the text, align to Center of cell
         



    // render PDF to browser
    $uniqid = uniqid();
    $today = date("d_m_Y");    
    $root = Storage::disk('dfiles')->path('');
    $current_exam_folder = str_replace('/','_',$bewerber->entrance_exam->zeichen);
    $sub_folders = "bescheide\\entrance_exams\\{$bewerber->entrance_exam->degree_en}_{$current_exam_folder}";
    if (preg_match('/^[\/\w\-. ]+$/', $name)) {
        $filename = "bestanden_{$bewerbungs_nummer}_{$name}_{$today}_{$uniqid}.pdf";
    } else {
        $filename = "bestanden_{$bewerbungs_nummer}_{$today}_{$uniqid}.pdf";
    } 
    $local_file = $root.$sub_folders."\\".$filename;
    if (!is_dir($root.$sub_folders)) {
        mkdir($root.$sub_folders,0777, true);
    }
    $pdf->Output('F', $local_file);
    return ["local_file"=>$local_file, "disk"=>"dfiles", "sub_folder"=>$sub_folders, "filename"=>$filename,"fragment"=>$sub_folders."\\".$filename];

}



?>