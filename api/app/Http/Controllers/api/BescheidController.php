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
use App\Models\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Auth;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Models\FormElement;
use App\Models\FormBescheidSettings;

require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_1.php');
require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_2.php');
require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_3.php');


class BescheidController extends Controller
{
 
    private function groupSubmissions($submissions, $el_id) {
        $submissions_grouped = [];
        foreach($submissions as $submission) {
            if($submission->_data[$el_id]==NULL) {
                continue;
            }
            if(!array_key_exists($submission->_data[$el_id], $submissions_grouped)) {
                $submissions_grouped[$submission->_data[$el_id]] = [];
            }
            $submissions_grouped[$submission->_data[$el_id]][] = $submission;
        }
        return $submissions_grouped;
    }

    private function LVMlynski_2($common_val,$submissions,$form,$root) {
        $file_fragments = ["bescheide","Lehrverpflichtung","Mlynski_2",$form->id];
        if(!file_exists(implode('\\',[$root,...$file_fragments]))) {
            mkdir(implode('\\',[$root,...$file_fragments]), 0777, true);
        }
        $filename = $common_val.".pdf";
        $prof_name_el_id = '832';
        $inst_ref_el_id = '836';
        $inst_name_el_id = '306';
        $institut = $submissions[0]->data[$form->bescheid_common_el_id]['data']->data[$inst_ref_el_id]['data']->data[$inst_name_el_id]['data'];
        $professor = $submissions[0]->data[$form->bescheid_common_el_id]['data']->data[$prof_name_el_id]['data'];
        $semester = $form->bescheid_common_data['semester'];
        $file_pdf = \Lehrverpflichtung\Bescheid_2\create(
            implode('\\',[$root,...$file_fragments,$filename]),
        ); 
        
        return [
            'common_val'=>$common_val,
            'common_val_text'=>$professor,
            'name'=>'Mlynski 2',
            'url'=>URL::signedRoute(
                'file_hosting', ['type'=>'bescheid','fragment'=>implode("\\",[...$file_fragments,$filename]),"disk"=>"dfiles"]
            ),
        ];
    }

    private function make_form_groupedby_bescheide($request,$user,$form) {
        $root = Storage::disk('dfiles')->path('');

        $submissions = $form->getSubmissions($user);
        $common_el_id = $form->bescheid_common_el_id;
        $group_by = $submissions[0]->data[$common_el_id]['label'];
        $submissions_grouped = $this->groupSubmissions($submissions, $common_el_id);

        $bescheide = [];
        foreach($submissions_grouped as $common_val=>$submissions_group) {
            if($common_val==NULL) {
                continue;
            }
            $group_bescheide = ['group_by'=>$group_by,'common_val'=>$common_val,'common_val_text'=>NULL,'bescheide'=>[]];
            if(in_array('Ml_1',$form->bescheide)) {
                $Ml_1 = $this->LVMlynski_1($common_val,$submissions_group,$form,$root);
                $common_val_text = $Ml_1['common_val_text'];
                $group_bescheide['bescheide'][] = $Ml_1;
                $group_bescheide['common_val_text'] = $common_val_text;
            }
            if(in_array('Ml_2',$form->bescheide)) {
                $Ml_2 = $this->LVMlynski_2($common_val,$submissions_group,$form,$root);
                $common_val_text = $Ml_2['common_val_text'];
                $group_bescheide['bescheide'][] = $Ml_2;
                $group_bescheide['common_val_text'] = $common_val_text;
            }
            if(!$common_val_text) {
                continue;
            }
            $bescheide[] = $group_bescheide;
        }

        return $bescheide;
    }

    private function LVMlynski_1($common_val,$submissions,$form,$root) {
        $file_fragments = ["bescheide","Lehrverpflichtung","Mlynski_1",$form->id];
        if(!file_exists(implode('\\',[$root,...$file_fragments]))) {
            mkdir(implode('\\',[$root,...$file_fragments]), 0777, true);
        }
        $filename = $common_val.".pdf";
        $prof_name_el_id = '832';
        $inst_ref_el_id = '836';
        $inst_name_el_id = '306';
        $institut = $submissions[0]->data[$form->bescheid_common_el_id]['data']->data[$inst_ref_el_id]['data']->data[$inst_name_el_id]['data'];
        $professor = $submissions[0]->data[$form->bescheid_common_el_id]['data']->data[$prof_name_el_id]['data'];
        $semester = $form->bescheid_common_data['semester'];
        \Lehrverpflichtung\Bescheid_1\create(
            $submissions,
            $form,
            implode('\\',[$root,...$file_fragments,$filename]),
            $professor,
            $institut,
            $semester
        ); 
        
        return [
            'common_val'=>$common_val,
            'common_val_text'=>$professor,
            'name'=>'Mlynski 1',
            'url'=>URL::signedRoute(
                'file_hosting', ['type'=>'bescheid','fragment'=>implode("\\",[...$file_fragments,$filename]),"disk"=>"dfiles"]
            ),
        ];
    }

    private function getValue($elements, $submissions, $el_id, $value) {
        $element = $elements[$el_id];
        if(!$element->input) {
            return NULL;
        }
        if($element->component==='SelectReferenceElement') {
            return $submissions[$element->data['formId']]->first(function ($submission) use($value) {
                return $submission->id==$value;
            });
        }
        if($element->component==='SelectElement') {
            return $element->data['data'][$value];
        }
        return $value;
    }
    
    private function ml_1($submission_group,$form, $user, $settings,$common_val,$elements,$submissions,$common_val_text) {
        // abort(response()->json($settings,401));
        // Filter submissions according to semester input element (812) of form 192 (0: SS, 1: WS, 2: ww)
        foreach($submission_group as $key=>$submission) {
            if($submission->_data[$settings->compound_form_data["sem_input"]]!=$settings->compound_form_data["sem"] && $submission->_data[$settings->compound_form_data["sem_input"]]!=2) {
                unset($submission_group[$key]);
            }
        }
        $submission_group = array_values($submission_group);



        $root = Storage::disk('dfiles')->path('');
        $file_fragments = ["bescheide","Lehrverpflichtung","ml_1",$form->id];


        if(!file_exists(implode('\\',[$root,...$file_fragments]))) {
            mkdir(implode('\\',[$root,...$file_fragments]), 0777, true);
        }
        $filename = $common_val.".pdf";
        
        $professor = ["_data"=>[$settings->compound_form_data['professor'][0]['el']=>$common_val]];
        foreach($settings->compound_form_data['professor'] as $step) {
            
            $professor = $this->getValue($elements,$submissions, $step['el'],$professor["_data"][$step["el"]]);
        }
        $institut = ["_data"=>[$settings->compound_form_data['institut'][0]['el']=>$common_val]];
        foreach($settings->compound_form_data['institut'] as $step) {
            
            $institut = $this->getValue($elements,$submissions, $step['el'],$institut["_data"][$step["el"]]);
        }
        $semester = $settings->compound_form_data['semester'];
        $file = \Lehrverpflichtung\Bescheid_1\create(
            $submission_group,
            $elements,
            $submissions,
            implode('\\',[$root,...$file_fragments,$filename]),
            $professor,
            $institut,
            $semester,
            $settings,
        ); 
        $bescheid = [
            'common_val'=>$common_val,
            'name'=>$settings->name,
            'url'=>URL::signedRoute(
                'file_hosting', ['type'=>'bescheid','fragment'=>implode("\\",[...$file_fragments,$filename]),"disk"=>"dfiles"]
            ),
        ];
        // }
        return ["bescheid"=>$bescheid,"file"=>$file];
    }

    private function ml_2($submission_group,$form,$user,$settings,$common_val,$elements,$submissions,$common_val_text) {
        $root = Storage::disk('dfiles')->path('');
        $file_fragments = ["bescheide","Lehrverpflichtung","ml_2",$form->id];
        if(!file_exists(implode('\\',[$root,...$file_fragments]))) {
            mkdir(implode('\\',[$root,...$file_fragments]), 0777, true);
        }
        $filename = $common_val.".pdf";
        $professor = ["_data"=>[$settings->compound_form_data['professor'][0]['el']=>$common_val]];
        foreach($settings->compound_form_data['professor'] as $step) {
            
            $professor = $this->getValue($elements,$submissions, $step['el'],$professor["_data"][$step["el"]]);
        }
        $institut = ["_data"=>[$settings->compound_form_data['institut'][0]['el']=>$common_val]];
        foreach($settings->compound_form_data['institut'] as $step) {
            
            $institut = $this->getValue($elements,$submissions, $step['el'],$institut["_data"][$step["el"]]);
        }
        $semester = $settings->compound_form_data['semester'];
        $file = \Lehrverpflichtung\Bescheid_2\create(
            $submission_group,
            implode('\\',[$root,...$file_fragments,$filename]),
            $elements,
            $submissions,
            $institut,
            $professor,
            $semester,
        ); 
        $bescheid = [
            'common_val'=>$common_val,
            'name'=>$settings->name,
            'url'=>URL::signedRoute(
                'file_hosting', ['type'=>'bescheid','fragment'=>implode("\\",[...$file_fragments,$filename]),"disk"=>"dfiles"]
            ),
        ];
        return ["bescheid"=>$bescheid,"file"=>$file];
    }


    private function _getValRecursively($element, $value) {
        if($value===null) {
            return [];
        }
        $a = [];
        if($element->component=='SelectReferenceElement') {
            $data = [];
            $ref_sub = Submission::findOrFail($value);
            foreach($element->data['formElementIds'] as $ref_el_id) {
                $ref_el = FormElement::findOrFail($ref_el_id);
                $ref_value = $ref_sub->_data[$ref_el_id];
                $data[] = $this->_getValRecursively($ref_el, $ref_value);
            }
            return implode(' ', $data);
        } else if($element->component=='SelectElement') {
            return [collect($element->data['data'])->first(function($item) use($value) {
                return $item['id']==$value;
            })['name']];
        } else {
            return $value;
        }
    }

    private function ml($settings,$user,$form,$bescheide=[]) {

        $group_by_el = FormElement::findOrFail($settings->group_by_el_id);
        $ml_1_settings = FormBescheidSettings::findOrFail($settings->compound_form_data["ml_1"]);
        $ml_1_form = Form::getForm($ml_1_settings->form_id_containing_bescheid_data,$user);
        $ml_1_data = $ml_1_form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user);
        $elements_ml_1 = [];
        foreach($ml_1_data[0] as $form_id=>$form_elements) {
            $elements_ml_1 += $form_elements;
        }
        $ml_1_submissions_grouped = $this->groupSubmissions($ml_1_data[1][$ml_1_form->id], $ml_1_settings->group_by_el_id);
        $ml_1_files = [];
        foreach($ml_1_submissions_grouped as $common_val=>$submission_group) {
            if($common_val==NULL) {
                continue;
            }
            $common_val_text = $this->_getValRecursively($group_by_el,$common_val);
            $ml_1 = $this->ml_1($submission_group,$form, $user, $ml_1_settings,$common_val,$elements_ml_1,$ml_1_data[1],$common_val_text);
            $ml_1_files[$common_val_text] = $ml_1["file"];
        }

        $ml_2_settings = FormBescheidSettings::findOrFail($settings->compound_form_data["ml_2"]);
        $ml_2_form = Form::getForm($ml_2_settings->form_id_containing_bescheid_data,$user);
        $ml_2_data = $ml_2_form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user);
        $elements_ml_2 = [];
        foreach($ml_2_data[0] as $form_id=>$form_elements) {
            $elements_ml_2 += $form_elements;
        }
        $ml_2_submissions_grouped = $this->groupSubmissions($ml_2_data[1][$ml_2_form->id], $ml_2_settings->group_by_el_id);
        $ml_2_files = [];
        foreach($ml_2_submissions_grouped as $common_val=>$submission_group) {
            if($common_val==NULL) {
                continue;
            }
            $common_val_text = $this->_getValRecursively($group_by_el,$common_val);
            $ml_2 = $this->ml_2($submission_group,$form, $user, $ml_2_settings,$common_val,$elements_ml_2,$ml_2_data[1],$common_val_text);
            $ml_2_files[$common_val_text] = $ml_2["file"];
        }


        $ml_form = Form::getForm($settings->form_id_containing_bescheid_data,$user);
        $ml_data = $ml_form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user);
        $elements_ml = [];
        foreach($ml_data[0] as $form_id=>$form_elements) {
            $elements_ml += $form_elements;
        }
        $ml_submissions_grouped = $this->groupSubmissions($ml_data[1][$ml_form->id], $settings->group_by_el_id);
        foreach($ml_submissions_grouped as $common_val=>$submission_group) {
            if($common_val==NULL) {
                continue;
            }
            $common_val_text = $this->_getValRecursively($group_by_el,$common_val);
            if(!array_key_exists($common_val_text,$bescheide)) {
                $bescheide[$common_val_text] = [];
            }

            $root = Storage::disk('dfiles')->path('');
            $file_fragments = ["bescheide","Lehrverpflichtung","ml",$form->id];
            $filename = $common_val.".pdf";
            if(!file_exists(implode('\\',[$root,...$file_fragments]))) {
                mkdir(implode('\\',[$root,...$file_fragments]), 0777, true);
            }
            $professor = ["_data"=>[$settings->compound_form_data['professor'][0]['el']=>$common_val]];
            foreach($settings->compound_form_data['professor'] as $step) {
                
                $professor = $this->getValue($elements_ml,$ml_data[1], $step['el'],$professor["_data"][$step["el"]]);
            }
            $institut = ["_data"=>[$settings->compound_form_data['institut'][0]['el']=>$common_val]];
            foreach($settings->compound_form_data['institut'] as $step) {
                
                $institut = $this->getValue($elements_ml,$ml_data[1], $step['el'],$institut["_data"][$step["el"]]);
            }
            $semester = $settings->compound_form_data['semester'];
            if(array_key_exists($common_val_text, $ml_1_files)) {
                if(array_key_exists($common_val_text, $ml_2_files)){
                    $_ml_1 = $ml_1_files[$common_val_text];
                    $_ml_2 = $ml_2_files[$common_val_text];
                } else {
                    $_ml_1 = $ml_1_files[$common_val_text];
                    $_ml_2 = null;
                }
            } else {
                if(array_key_exists($common_val_text, $ml_2_files)){
                    $_ml_1 = null;
                    $_ml_2 = $ml_2_files[$common_val_text];
                } else {
                    $_ml_1 = null;
                    $_ml_2 = null;
                }  
            }
            // return $settings->compound_form_data;
            // return [$submission_group,implode('\\',[$root,...$file_fragments,$filename]),$professor,$institut];
            \Lehrverpflichtung\Bescheid_3\create(
                $submission_group,
                implode('\\',[$root,...$file_fragments,$filename]),
                $professor,
                $institut,
                $semester,
                $_ml_1,
                $_ml_2,
                $settings->compound_form_data,
            );
            $bescheid = [
                'common_val'=>$common_val,
                'name'=>"Mlynski",
                'url'=>URL::signedRoute(
                    'file_hosting', ['type'=>'bescheid','fragment'=>implode("\\",[...$file_fragments,$filename]),"disk"=>"dfiles"]
                ),
            ];
            $bescheide[$common_val_text][] = $bescheid;
        }
        return $bescheide;

    }


    public function index(Request $request) {
        $user = Auth::user();
        $form = Form::getForm($request->form_id,$user);

        $bescheide = [];
        
        foreach($form->form_bescheid_settings as $settings) {
            $groupby_name = $settings->group_by_name;
            if(!array_key_exists($groupby_name,$bescheide)) {
                $bescheide[$groupby_name] = [];
            }
            $bescheide[$groupby_name] = $this->{"$settings->name"}($settings,$user,$form,$bescheide[$groupby_name]);
        }

        return response()->json($bescheide);
    }

    private function anrede($bewerber, $lang="de") {
        if($bewerber->getAttribute("Form of address")=="Herr" && $lang=="de") {
            return "Herr";
        }
        if($bewerber->getAttribute("Form of address")=="Frau" && $lang=="de") {
            return "Frau";
        }
        if($bewerber->getAttribute("Form of address")=="Herr" && $lang=="en") {
            return "Mr";
        }
        if($bewerber->getAttribute("Form of address")=="Frau" && $lang=="en") {
            return "Mrs";
        }
        if($lang=="de") {
            return "Sehr geehrte Dame, sehr geehrter Herr,";
        }
        if($lang=="en") {
            return "Dear,";
        }
    }

    private function bewerberReplacePlaceholders($input_text, $bewerber) {
        $text = $input_text;
        $BEWERBUNGSNUMMER_PURE = $bewerber->getAttribute("Number");
        $BEWERBUNGSNUMMER_PURE = str_replace(',','',$BEWERBUNGSNUMMER_PURE);
        $BEWERBUNGSNUMMER_PURE = str_replace('.','',$BEWERBUNGSNUMMER_PURE);
        if($bewerber->entrance_exam->getAttribute("degree_en")==="Mechanical engineering") {
            $exam_type_short = "Mach";
        } else {
            $exam_type_short = "MatWerk";
        }

        $placeholders = [
            "%NAME%"=>$bewerber->getAttribute("Last name"),
            "%VORNAME%"=>$bewerber->getAttribute("First name"),
            "%EMAIL%"=>$bewerber->getAttribute("Email"),
            "%ANREDE_DE%"=>$this->anrede($bewerber, "de"),
            "%ANREDE_EN%"=>$this->anrede($bewerber, "en"),
            "%BEWERBUNGSNUMMER%"=>$bewerber->getAttribute("Number"),
            "%BEWERBUNGSNUMMER_PURE%"=>$BEWERBUNGSNUMMER_PURE,
            "%DEGREE_DE%"=>$bewerber->entrance_exam->getAttribute("degree_de"),
            "%DEGREE_EN%"=>$bewerber->entrance_exam->getAttribute("degree_en"),
            "%ILIAS%"=>$bewerber->getAttribute("ILIAS"),
            "%EXAM_DATE_DE%"=>$bewerber->entrance_exam->getAttribute("exam_date")->format('d.m.Y'),
            "%EXAM_DATE_EN%"=>$bewerber->entrance_exam->getAttribute("exam_date")->format('m/d/Y'),
            "%EXAM_TIME%"=>$bewerber->entrance_exam->getAttribute("exam_time"),
            "%EMAIL_NO_DOMAIN%"=>explode("@",$bewerber->getAttribute("Email"))[0],
            "%FIRSTNAME_NO_SPACES%"=>implode("",explode(" ",$bewerber->getAttribute("First name"))),
            "%ILIAS_COURS_URL%"=>$bewerber->entrance_exam->getAttribute("exam_cours_url"),
            "%EXAM_TYPE_SHORT%"=>$exam_type_short,
            // "%ILIAS_COURS_URL_EN%"=>$bewerber->entrance_exam->getAttribute("exam_cours_url_en"),
        ];
        foreach($placeholders as $placeholder=>$value) {
            $text = str_replace($placeholder,$value,$text);
        }
        return $text;
    }

    public function makeBewerberBescheid(Request $request) {
        $user = Auth::user();
        $type = $request->get("name");
        // $lang = $request->get("lang");
        $mail = (bool) $request->get("mail");
        $mail_subject = $request->get("mail_subject");
        $mail_attachments = $request->file('attachment_files');
        $mail_content = $request->get("mail_content");
        $bewerber_ids = json_decode($request->get("bewerber_ids"),true);
        $bewerbers = Bewerber::whereIn('id',$bewerber_ids)->get();
        $files = [];
        if($mail_attachments!==NULL) {
            foreach($mail_attachments as $attachment) {
                $path = $attachment->store("mail", 'dfiles');
                $files[] = ['disk'=>'dfiles','fragment'=>$path,'name'=>$attachment->getClientOriginalName()];
            }
        }

        if($type=="Email Only") {
            foreach($bewerbers as $bewerber) {
                $subject = $this->bewerberReplacePlaceholders($mail_subject,$bewerber);
                $content = $this->bewerberReplacePlaceholders($mail_content,$bewerber);
                $this->mailBewerber($subject,$content,$files,$bewerber,$user);
            }
        } else if($type==="Admission") {
            foreach($bewerbers as $bewerber) {
                $local_files = $files;
                $file = Admission\create($bewerber);

                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$file["fragment"],'disk'=>'dfiles']
                );

                $bescheid = new Bescheid([
                    "name"=>"Admission",
                    "file"=>["url"=>$url,"name"=>"Admission.pdf"],
                ]);
                $bewerber->bescheid()->save($bescheid);
                $local_files[] = ['disk'=>$file['disk'],'fragment'=>$file["fragment"],"name"=>$file['filename']];
                if($mail) {
                    $subject = $this->bewerberReplacePlaceholders($mail_subject,$bewerber);
                    $content = $this->bewerberReplacePlaceholders($mail_content,$bewerber);
                    $this->mailBewerber($subject,$content,$local_files,$bewerber,$user);
                }
            }

        } else if($type==="Rejection") {
            foreach($bewerbers as $bewerber) {
                $local_files = $files;
                $file = Rejection\create($bewerber);
                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$file["fragment"],'disk'=>'dfiles']
                );
                $bescheid = new Bescheid([
                    "name"=>"Rejection",
                    "file"=>["url"=>$url,"name"=>"Admission.pdf"],
                ]);
                $bewerber->bescheid()->save($bescheid);
                $local_files[] = ['disk'=>$file['disk'],'fragment'=>$file["fragment"],"name"=>$file['filename']];
                if($mail) {
                    $subject = $this->bewerberReplacePlaceholders($mail_subject,$bewerber);
                    $content = $this->bewerberReplacePlaceholders($mail_content,$bewerber);
                    $this->mailBewerber($subject,$content,$local_files,$bewerber,$user);
                }
            }
        } else if($type==="Bestanden") {
            foreach($bewerbers as $bewerber) {
                $local_files = $files;
                $file = Bestanden\create($bewerber);
                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$file["fragment"],'disk'=>'dfiles']
                );
                $bescheid = new Bescheid([
                    "name"=>"Bestanden",
                    "file"=>["url"=>$url,"name"=>"Bestanden.pdf"],
                ]);
                $bewerber->bescheid()->save($bescheid);
                $local_files[] = ['disk'=>$file['disk'],'fragment'=>$file["fragment"],"name"=>$file['filename']];
                if($mail) {
                    $subject = $this->bewerberReplacePlaceholders($mail_subject,$bewerber);
                    $content = $this->bewerberReplacePlaceholders($mail_content,$bewerber);
                    $this->mailBewerber($subject,$content,$local_files,$bewerber,$user);
                }
            }
        } else if($type==="Nicht Bestanden") {
            foreach($bewerbers as $bewerber) {
                $local_files = $files;
                $file = NichtBestanden\create($bewerber);
                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$file["fragment"],'disk'=>'dfiles']
                );
                $bescheid = new Bescheid([
                    "name"=>"Nicht Bestanden",
                    "file"=>["url"=>$url,"name"=>"Nicht Bestanden.pdf"],
                ]);
                $bewerber->bescheid()->save($bescheid);
                $local_files[] = ['disk'=>$file['disk'],'fragment'=>$file["fragment"],"name"=>$file['filename']];
                if($mail) {
                    $subject = $this->bewerberReplacePlaceholders($mail_subject,$bewerber);
                    $content = $this->bewerberReplacePlaceholders($mail_content,$bewerber);
                    $this->mailBewerber($subject,$content,$local_files,$bewerber,$user);
                }
            }
        } else if($type==="Nicht Bestanden Nicht Teilgenommen") {
            foreach($bewerbers as $bewerber) {
                $local_files = $files;
                $file = NichtBestandenNichtTeilgenommen\create($bewerber);
                $url = URL::signedRoute(
                    'file_hosting', ['fragment'=>$file["fragment"],'disk'=>'dfiles']
                );
                $bescheid = new Bescheid([
                    "name"=>"Nicht Bestanden Nicht Teilgenommen",
                    "file"=>["url"=>$url,"name"=>"Nicht Bestanden Nicht Teilgenommen.pdf"],
                ]);
                $bewerber->bescheid()->save($bescheid);
                $local_files[] = ['disk'=>$file['disk'],'fragment'=>$file["fragment"],"name"=>$file['filename']];
                if($mail) {
                    $subject = $this->bewerberReplacePlaceholders($mail_subject,$bewerber);
                    $content = $this->bewerberReplacePlaceholders($mail_content,$bewerber);
                    $this->mailBewerber($subject,$content,$local_files,$bewerber,$user);
                }
            }
        }
        
        return response()->json($files);
    }

    private function mailBewerber($subject,$content,$files,$bewerber,$user) {
        $to_addresses = [$bewerber->Email];
        $mail = new Email([
            "subject"=>$subject,
            "body"=>$content,
            "files"=>$files,
            "to_addresses"=>$to_addresses,
            "from_address"=>"portal@mach.kit.edu",
            "from_alias"=>"MACH-Portal",
        ]);
        $mail->user()->associate($user);
        $mail->save();
        $bcc = [
            ["address"=>"zk-aprf@mach.kit.edu","alias"=>"zk-aprf"],
        ];
        $mail->send(html: true, bcc: $bcc);
    }

}

