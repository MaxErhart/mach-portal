<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bescheid;
use App\Models\Bewerber;

use App\Classes\fpdf\fpdf;
use App\Classes\fpdi\fpdi;
use App\Classes\Pdf2text\Pdf2text;
require_once(base_path().'\app\pdf_templates\admission.php');
require_once(base_path().'\app\pdf_templates\rejection.php');
require_once(base_path().'\app\pdf_templates\bestanden.php');
require_once(base_path().'\app\pdf_templates\nicht_bestanden.php');
require_once(base_path().'\app\pdf_templates\nicht_bestanden_nicht_teilgenommen.php');
use \PdfTemplates\Admission;
use \PdfTemplates\Rejection;
use \PdfTemplates\Bestanden;
use \PdfTemplates\NichtBestanden;
use \PdfTemplates\NichtBestandenNichtTeilgenommen;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class BescheidController extends Controller
{
    
    public function store(Request $request)
    {


        

        $request->validate([
            "name"=>"required|max:255",
            "bewerber_ids"=>"required",
        ]);
        

        $bewerber_ids = json_decode($request->get("bewerber_ids"));
        $bewerbers = Bewerber::whereIn("id", $bewerber_ids)->get();

        foreach($bewerbers as $bewerber) {

            if(filter_var($request->get("mail"), FILTER_VALIDATE_BOOLEAN)) {
                $header = "Content-type: text/html; charset=iso-8859-1\n";
                $header .= "From: zk-aprf@mach.kit.edu";
                $firstname = $bewerber->Vorname;
                $lastname = $bewerber->Name;
                $name = "{$firstname} {$lastname}";
                if($firstname=="'-") {
                    $name = $lastname;
                } else if($lastname=="'-") {
                    $name = $firstname;
                    $lastname = $firstname;
                }                 
                if($bewerber->Geschlecht=="M") {
                    $anrede_de = "Sehr geehrter Herr {$name},";
                    $anrede_en = "Dear Mr {$name},";
                } else if($bewerber->Geschlecht=="F") {
                    $anrede_de = "Sehr geehrte Frau {$name},";
                    $anrede_en = "Dear Ms {$name},";                    
                } else {
                    $anrede_de = "Sehr geehrte Dame, sehr geehrter Herr,";
                    $anrede_en = "Hello,"; 
                }
                $placeholders = [
                    "%FIRSTNAME_NO_SPACES%"=>str_replace(" ","",$bewerber->Vorname),
                    "%EMAIL_NO_DOMAIN%"=>explode("@", $bewerber->getAttribute('KIT-E-Mail'))[0],
                    "%NAME%"=>$bewerber->Name,
                    "%VORNAME%"=>$bewerber->Vorname,
                    "%EMAIL%"=>$bewerber->getAttribute('KIT-E-Mail'),
                    // "%DEGREE%"=>$bewerber->Studiengang,
                    "%BEWERBUNGSNUMMER%"=>$bewerber->getAttribute('Bewerbungs-nummer'),
                    "%DEGREE_DE%"=>$bewerber->entrance_exam->degree_de,
                    "%DEGREE%"=>$bewerber->entrance_exam->degree_en,
                    "%DEGREE_EN%"=>$bewerber->entrance_exam->degree_en,
                    "%ILIAS%"=>$bewerber->ILIAS,
                    "%EXAM_DATE%"=>$bewerber->entrance_exam->exam_date->format("d.m.Y"),
                    "%EXAM_TIME%"=>$bewerber->entrance_exam->exam_time->format("H:i"),
                    "%ANREDE_DE%"=>$anrede_de,
                    "%ANREDE_EN%"=>$anrede_en,
                ]; 

                $email_content = $request->get('mail_content');
                foreach($placeholders as $placeholder=>$value) {
                    $email_content = str_replace($placeholder, $value, $email_content);
                }
                $email_content = utf8_decode($email_content);
                $email = new PHPMailer();
                $email->SetFrom('zk-aprf@mach.kit.edu', 'MACH-zk-aprf');
                $email->Subject = utf8_decode($request->get('mail_subject'));
                $email->Body = $email_content;
                $email->AddAddress($bewerber->getAttribute('KIT-E-Mail'));
                $email->addBcc('zk-aprf@mach.kit.edu');
            }
            

            if (!($request->get("name")=="Email Only") && !($request->get("name")=="Email mit Anhang")){
                if($request->get("name")=="Admission") {
                    $file_pdf = \PdfTemplates\Admission\create(
                        $bewerber,
                        $request->get("lang"),
                    );                
                } elseif($request->get("name")=="Rejection") {
                    $file_pdf = \PdfTemplates\Rejection\create(
                        $bewerber,
                        $request->get("lang"),
                    );                
                } elseif($request->get("name")=="Bestanden") {
                    $file_pdf = \PdfTemplates\Bestanden\create(
                        $bewerber,
                        $request->get("lang"),
                    );
                    if(str_contains($bewerber->entrance_exam->degree_de, 'Maschinenbau')) {
                        $email->addCc('studserv-team4@sle.kit.edu');
                        $email->addCc('zk-master-mach@mach.kit.edu');
                    } else if(str_contains($bewerber->entrance_exam->degree_de, 'Materialwissenschaft und Werkstofftechnik')) {
                        $email->addCc('zk-matwerk@mach.kit.edu');
                    }
                } elseif($request->get("name")=="Nicht Bestanden") {
                    $file_pdf = \PdfTemplates\NichtBestanden\create(
                        $bewerber,
                        $request->get("lang"),
                    );
                                
                }  elseif($request->get("name")=="Nicht Bestanden Nicht Teilgenommen") {
                    $file_pdf = \PdfTemplates\NichtBestandenNichtTeilgenommen\create(
                        $bewerber,
                        $request->get("lang"),
                    );              
                }
                $bescheid = new Bescheid([
                    "name"=>$request->get("name"),
                    "file_pdf"=>$file_pdf[0],
                ]);
                $bescheid->bewerber()->associate($bewerber)->save();
                $email->AddAttachment($file_pdf[0]);

            } 

            if ($request->get("name")=="Email mit Anhang"){
                $email->AddAttachment("D:\inetpub\MPortal\pdf_templates\bescheide\matwerk_aufnahmepruefung_220914_anschreiben.pdf");
                $email->AddAttachment("D:\inetpub\MPortal\pdf_templates\bescheide\matwerk_entrance_exam_information_220914_letter.pdf");
            }

            $attachments = $request->file("attachment_files");
            if($attachments != NULL) {
                foreach($attachments as $attachment) {
                    $path = $attachment->store('file');
                    $path = str_replace("/", "\\", $path);
                    $base_path = storage_path('app');
                    $path = "{$base_path}\\{$path}";
                    // array_push($paths, "{$base_path}\\{$path}");                
                    $email->AddAttachment($path);
                }
            }


            if(filter_var($request->get("mail"), FILTER_VALIDATE_BOOLEAN)) {
                $email->Send();                               
            }

        }


        return response()->json($bewerber);
    }

}

