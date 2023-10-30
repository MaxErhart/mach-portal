<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Submission;
use App\Models\Form;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use DB;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\URL;
use \Datetime;

use Illuminate\Support\Facades\Storage;
require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_1.php');

class SubmissionController extends Controller
{
    private $settings = [
        "email" => [
            "regex" => '/^(([^<>()[\]\.,;:\s@"]+(\.[^<>()[\]\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/',
            "error_message" => "Wrong email input"
        ],
        "text" => [
            "regex" => '/.*/',
            "error_message" => "Wrong text input"            
        ],
        "number" => [
            "regex" => '/^[0-9]*$/',
            "error_message" => "Wrong number input"            
        ],
        "integer" => [
            "regex" => '/^[0-9]*$/',
            "error_message" => "Wrong number input"            
        ],
        "float" => [
            "regex" => '/^[+-]?([0-9]*[.|,])?[0-9]*$/',
            "error_message" => "Wrong number input"            
        ],
        "date" => [
            "regex" => '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/',
            "error_message" => "Wrong date input"            
        ],              
    ];

    private function getReferences($form) {
        $references = [];
        foreach($form->base_form_elements as $form_element) {
            if($form_element->component!=="SelectReferenceElement") {
                continue;
            }
            $reference_form = Form::findOrFail($form_element->data['formId']);
            $reference_form->base_form_elements;
            $reference_submissions = $this->getFormSubmissions($form_element->data["formId"]);
            $references[$form_element->id] = ['submissions'=>$reference_submissions,'form'=>$reference_form];
        }
        return $references;
    }


    private function parseToString($value, $form_element) {
      if($form_element->component==='SelectReferenceElement') {
        $compound_val = [];
        foreach($form_element->data['formElementIds'] as $id) {
            $compound_val[] = $value->_data[$id];
        }
        return implode(" ", $compound_val);
      }
      return $value;
    }

    private function parse($value, $form_element, $references) {
        if($form_element->component==='SelectReferenceElement') {
            $reference_sub = $references[$form_element->id]['submissions']->filter(function($submission) use($value){
                return $submission->id===$value;
            })->first();

            return $reference_sub;
        }
        return $value;
    }

    private function makeBescheid($common_val,$group,$form) {
        $root = "D:\inetpub\dfiles";
        // $today = date("d_m_Y");
        $filename = $common_val.".pdf";
        $output_file_fragments = [$root,"auto_bescheide", $form->id, $filename];
        \Lehrverpflichtung\Bescheid_1\create(
            $common_val,
            $group,
            $form,
            implode("\\",$output_file_fragments),
        );
        return $filename;
    }
    public function indexBescheide(Request $request) {
        $user = Auth::user();
        $form = Form::findOrFail($request->form_id);
        if($form->bescheid===NULL || $form->bescheid_common_el_id===NULL) {
            return [];
        }
        $submissions = $form->getSubmissions($user);
        $submissions_grouped = $this->groupSubmissions($submissions, $form->bescheid_common_el_id);
        $bescheide = [];
        foreach($submissions_grouped as $common_val=>$group) {
            if($common_val===0) {
                continue;
            }
            $filename = $this->makeBescheid($common_val,$group,$form);
            $bescheide[] = URL::signedRoute(
                'file_hosting', ['type'=>'auto_bescheide','form'=>$form->id,'file'=>$filename]
            );
        }
        return response()->json($bescheide);
    }

    private function groupSubmissions($submissions, $el_id) {
        $submissions_grouped = [];
        foreach($submissions as $submission) {
            if($submission->_data[$el_id]===NULL) {
                continue;
            }
            if(!array_key_exists($submission->_data[$el_id], $submissions_grouped)) {
                $submissions_grouped[$submission->_data[$el_id]] = [];
            }
            $submissions_grouped[$submission->_data[$el_id]][] = $submission;
        }
        return $submissions_grouped;
    }

    public function confirm($id, Request $request) {
        $submission = Submission::where('confirmation_id', $id)->get()[0];
        $submission->confirmed = true;
        $submission->save();
        return "Success";
    }


    public function getFormSubmissions($formId) {
        $user = Auth::user();
        $submissions = Submission::where('form_id', $formId)->get();
        $tmp = [];
        foreach($submissions as $key=>$submission) {
            $subRight = $submission->getPermission($user);
            if($subRight>0) {
                $submission->_data;
                $tmp[] = $submission;
            }
        }
        $submissions = collect($tmp);
        return $submissions;
    }





    public function index(Request $request)
    {

        $request->merge(["form_id"=>$request->form_id]);
        $request->validate([
            "form_id"=>"required|integer",
        ]);
        $user = Auth::user();
        $form = Form::findOrFail($request->form_id);

        $references = [];
        foreach($form->base_form_elements as $form_element) {
            if($form_element->component!=="SelectReferenceElement") {
                continue;
            }
            $reference_form = Form::findOrFail($form_element->data['formId']);
            $reference_form->base_form_elements;
            $reference_submissions = $this->getFormSubmissions($form_element->data["formId"]);
            $references[$form_element->id] = ['submissions'=>$reference_submissions,'form'=>$reference_form];
        }

        $submissions = Submission::where('form_id', $request->form_id)->get();
        // foreach($submissions as $submission) {
        //     $test = $submission->data();
        //     $submission->_data = $test;
        //     $submission->save();
        // }
        // return response()->json($submissions);



        $tmp = [];
        foreach($submissions as $key=>$submission) {
            $subRight = $submission->getPermission($user);
            if($subRight>0) {
                $tmp[] = $submission;
            }
        }
        $submissions = collect($tmp);

        foreach($submissions as $submission) {
            $submission->owner;
            $submission->form;
            foreach($submission->replies as $reply) {
                $reply->user;
            }
        }
        $file = null;

   
        
        // $bescheide=$this->makeBescheid($form, $user, $submissions, $references);

        return response()->json(['submissions'=>$submissions,'references'=>$references, 'file_export'=>$file]);
    }


    public function custom_validate($request, $form_elements) {
        $validation = [
            "type"=>0,
            "elements"=>[],
            "message"=>"Input error",
        ];
        $abort = false;
        foreach($form_elements as $form_element) {
            if($form_element->component=="InputElement") {
                $v = $request->get($form_element->id);
                if($form_element->data["required"] && ($v==null || $v=='')) {
                    $abort = true;
                    $validation["elements"][] = [
                        'status'=>400,
                        'message'=>"Field required",
                        'id'=>$form_element->id,
                    ];
                }
                if(!preg_match($this->settings[$form_element->data["type"]]["regex"], $v)) {
                    $abort = true;
                    $validation["elements"][] = [
                        'status'=>400,
                        'message'=>$this->settings[$form_element->data["type"]]["error_message"],
                        'id'=>$form_element->id,
                    ];
                }
            } else if($form_element->component=="FileUploadElement") {
                $v = $request->file($form_element->id);
                if($form_element->data["required"] && $v==null) {
                    $abort = true;
                    $validation["elements"][] = [
                        'status'=>400,
                        'message'=>"Field required",
                        'id'=>$form_element->id,
                    ];
                }
            } else if($form_element->component=="Checkbox") {
                $v = filter_var($request->get($form_element->id), FILTER_VALIDATE_BOOLEAN);
                if($form_element->data["required"] && !$v) {
                    $abort = true;
                    $validation["elements"][] = [
                        'status'=>400,
                        'message'=>"Field required",
                        'id'=>$form_element->id,
                    ];
                }
            } else if($form_element->component=="SelectElement") {
                $v = $request->get($form_element->id);
                if($form_element->data["required"] && ($v==null || $v=='')) {
                    $abort = true;
                    $validation["elements"][] = [
                        'status'=>400,
                        'message'=>"Field required",
                        'id'=>$form_element->id,
                    ];
                }
            } else if($form_element->component=="SelectReferenceElement") {
                $v = $request->get($form_element->id);
                if($form_element->data["required"] && ($v==null || $v=='')) {
                    $abort = true;
                    $validation["elements"][] = [
                        'status'=>400,
                        'message'=>"Field required",
                        'id'=>$form_element->id,
                    ];
                }
            } else {
                continue;
            }          
        }
        if($abort) {
            abort(response()->json($validation, 400));
        }
    }

    public function seen(Request $request, $id) {
        $submission = Submission::where('id', $id)->get()[0];
        $user = Auth::user();
        foreach($submission->replies as $reply) {
            if(!in_array($user->id, $reply->seen)) {
                $reply->seen = array_merge($reply->seen, [$user->id]);
                $reply->save();
            }
            $reply->user;
        }
        return response()->json($submission->replies);
    }

    public function store(Request $request)
    {

        $request->validate([
            "form_id"=>"Required|integer",
        ]);
        $form = Form::findOrFail($request->get("form_id"));
        
        if(!Auth::check() && !$form->no_login) {
            abort(response()->json([
                "message"=>"Unauthorized",
            ], 401));
        }

        if($form->no_login) {
            $user = User::findOrFail(4);
            $form->submit_permission = 2;
        } else {
            $user = Auth::user();
            $form->submit_permission = $form->getSubmitPermission($user);
        }

        if($form->submit_permission<2) {
            abort(response()->json([
                "message"=>"Unauthorized",
            ], 401));
        }
        $yesturday = new DateTime();
        $yesturday = $yesturday->modify('-1 day');
        if($form->deadline && $form->deadline<$yesturday && !$user->isAdmin() && $user->id!==$form->creator_id) {
            $validation = [
                "type"=>1,
                "message"=>"Deadline passed",
            ];
            abort(response()->json($validation, 400));
        }
        if(!$form->no_login && $form->multiple_submissions==0 && count($user->submissions()->where('form_id', $form->id)->get()->toArray())>0) {
            $validation = [
                "type"=>1,
                "message"=>"Multiple submissions not allowed",
            ];
            abort(response()->json($validation, 401));
        }
        $form_elements = $form->base_form_elements;
        $this->custom_validate($request, $form_elements);
     
        $data = [];
        foreach($form_elements as $form_element) {
            if($form_element->input==0) {
                continue;
            }
            if($request->get($form_element->id)=='NaN') {
                continue;
            }
            if($request->get($form_element->id)==NULL) {
                $data[$form_element->id] = NULL;
                continue;
            }
            if($form_element->component=="InputElement") {
                if($form_element->data["type"]=="date"){
                    $date = new DateTime($request->get($form_element->id));
                    $data[$form_element->id] = $date->format('Y-m-d');
                } else if($form_element->data["type"]=="number"||$form_element->data["type"]=="float"||$form_element->data["type"]=="integer") {
                    $data[$form_element->id] = floatval($request->get($form_element->id));
                } else {
                    $data[$form_element->id] = $request->get($form_element->id);
                }
            } else if($form_element->component=="SelectElement" || $form_element->component=="SelectReferenceElement") {
                $data[$form_element->id] = intval($request->get($form_element->id));
            } else if($form_element->component=="FileUploadElement") {
                $files = $request->file($form_element->id);
                $paths = [];
                if($files==NULL) {
                    $data[$form_element->id] = [];
                    continue;
                }
                $target_path = str_replace("\\", "/", $form_element->data['path']);
                foreach($files as $file) {
                    $rel_path = $file->store($target_path,'dfiles');
                    $url = Storage::disk('dfiles')->url($rel_path);

                    $paths[] = ['url'=> $url,'name'=>$file->getClientOriginalName()];
                }
                $data[$form_element->id] = $paths;


            } else if($form_element->component=="Checkbox") {
                $data[$form_element->id] = filter_var($request->get($form_element->id), FILTER_VALIDATE_BOOLEAN);
            }       
        }


        $newSubmission = new Submission();
        $owner = json_decode($request->get('submission_owner'),true);
        if(empty($owner)) {
            $owner = $user;
        } else if($owner['type']==="App\\Models\\User") {
            $owner = User::findOrFail($owner["id"]);
            if($owner->getUserPermissionOnUser($user)<3) {
                $owner = $user;
            }
        } else {
            $owner = Group::findOrFail($owner["id"]);
            if($owner->getUserPermissionOnGroup($user)<3) {
                $owner = $user;
            }
        }
        $newSubmission->owner()->associate($owner);
        $newSubmission->form()->associate($form);
        if($form->no_login) {
            $uniqId = uniqid();
            $newSubmission->confirmation_id = $newSubmission->id.$uniqId;
        }
        $newSubmission->_data = $data;
        $newSubmission->save();

        $newSubmission->getPermission($user);
    

        if($form->no_login && $form->email_element_id) {
            foreach($newSubmission->form_elements as $form_element) {
                if($form_element->id!==$form->email_element_id) {
                    continue;
                }
                $mailAddress = $form_element->pivot->data;
            }


            $subject = $form->no_login_email_subject;
            $subject = $this->replacePlaceholders($subject,$newSubmission);


            $body = $form->no_login_email_body;
            $body = $this->replacePlaceholders($body,$newSubmission);

            $body .= "<br><br>Please confirm your email address by clicking on the following link: https://www-3.mach.kit.edu/api/shib/mach-api/public/index.php/api/submissions/confirm/{$newSubmission->confirmation_id}";

            $email = new PHPMailer();
            $email->SetFrom('portal@mach.kit.edu', 'MACH-Portal');
            $email->Subject = utf8_decode($subject);


            $email->Body = utf8_decode($body);
            $email->AddAddress($mailAddress);
            $email->IsHTML(true);
            $email->AddBCC('portal@mach.kit.edu');
            $email->Send();
        }

        return response()->json($newSubmission);
    }

    private function replacePlaceholders($string,$submission) {
        $placeholders = [
        ];
        foreach($submission->form_elements as $form_element) {
            $placeholders["%{$form_element->id}%"] = $form_element->pivot->data;
        }
        foreach($placeholders as $placeholder=>$value) {
            $string = str_replace($placeholder, $value, $string);
        }
        return $string;
    }

    public function update(Request $request, $subId)
    {       
        $submission = Submission::findOrFail($subId);
        $user = Auth::user();

        $subRight = $submission->getPermission($user);
        if($subRight<2) {
            return response('Unauthorized.', 401);
        }
        $yesturday = new DateTime();
        $yesturday = $yesturday->modify('-1 day');
        if($submission->form->deadline && $submission->form->deadline<$yesturday && !$user->isAdmin() && $user->id!==$form->creator_id) {
            $validation = [
                "type"=>1,
                "message"=>"Deadline passed",
            ];
            abort(response()->json($validation, 400));
        }

        $form_elements = $submission->form->base_form_elements;
        $this->custom_validate($request, $form_elements);



        $data = [];
        foreach($form_elements as $form_element) {
            if($form_element->input==0) {
                continue;
            }
            if($request->get($form_element->id)=='NaN') {
                continue;
            }
            if($request->get($form_element->id)==NULL) {
                $data[$form_element->id] = NULL;
                continue;
            }
            if($form_element->component=="InputElement") {
                if($form_element->data["type"]=="date"){
                    $date = new DateTime($request->get($form_element->id));
                    $data[$form_element->id] = $date->format('Y-m-d');
                } else if($form_element->data["type"]=="number"||$form_element->data["type"]=="float"||$form_element->data["type"]=="integer") {
                    $data[$form_element->id] = floatval($request->get($form_element->id));
                } else {
                    $data[$form_element->id] = $request->get($form_element->id);
                }
            } else if($form_element->component=="SelectElement" || $form_element->component=="SelectReferenceElement") {
                $data[$form_element->id] = intval($request->get($form_element->id));
            } else if($form_element->component=="FileUploadElement") {
                $files = $request->file($form_element->id);
                $paths = [];
                if($files==NULL) {
                    $data[$form_element->id] = [];
                    continue;
                }
                $target_path = str_replace("\\", "/", $form_element->data['path']);
                foreach($files as $file) {
                    $rel_path = $file->store($target_path,'dfiles');
                    $url = Storage::disk('dfiles')->url($rel_path);

                    $paths[] = ['url'=> $url,'name'=>$file->getClientOriginalName()];
                }
                $data[$form_element->id] = $paths;


            } else if($form_element->component=="Checkbox") {
                $data[$form_element->id] = filter_var($request->get($form_element->id), FILTER_VALIDATE_BOOLEAN);
            }       
        }




        $submission->_data = $data;

        unset($submission->permission);
        $owner = json_decode($request->get('submission_owner'),true);
        if(empty($owner)) {
            $owner = $user;
        } else if($owner['type']==="App\\Models\\User") {
            $owner = User::findOrFail($owner["id"]);
            if($owner->getUserPermissionOnUser($user)<3) {
                $owner = $user;
            }
        } else {
            $owner = Group::findOrFail($owner["id"]);
            if($owner->getUserPermissionOnGroup($user)<3) {
                $owner = $user;
            }
        }
        if($owner->id!==$submission->owner->id) {
            $submission->owner()->associate($owner);
        }
        $submission->save();
        $submission->owner;
        foreach($submission->replies as $reply) {
            $reply->user;
        }
        $submission->getPermission($user);


        $submission->getPermission($user);


        return response()->json($submission);
    }


    public function destroy($id)
    {
        $submission = Submission::findOrFail($id);
        $form = $submission->form;
        $user = Auth::user();
        $subRight = $submission->getPermission($user);
        if($subRight<3) {
            return response('Unauthorized.', 401);
        }
        $yesturday = new DateTime();
        $yesturday = $yesturday->modify('-1 day');
        if($form->deadline && $form->deadline<$yesturday && !$user->isAdmin() && $user->id!==$form->creator_id) {
            $validation = [
                "type"=>1,
                "message"=>"Deadline passed",
            ];
            abort(response()->json($validation, 400));
        }
        $submission->form_elements()->detach();
        $submission->delete();

        $submissions = Submission::where('form_id', $form->id)->get();
        $tmp = [];
        foreach($submissions as $key=>$submission) {
            $subRight = $submission->getPermission($user);
            if($subRight>0) {
                $tmp[] = $submission;
            }
        }
        $submissions = collect($tmp);

        foreach($submissions as $submission) {
            $submission->owner;
            $submission->form;
            $submission->form_elements = $submission->form_elements()->orderBy('position', 'asc')->get();
        }

        $submissions->map(function($submission) {
            return $submission->form_elements->map(function($element) {
                $element->pivot->data = json_decode($element->pivot->data);
                return;
            });
        });
        return response()->json($submissions);
    }
    

    private function implodeNestedArray($array) {
        if(!is_array($array)) {
            return $array;
        } 
        $values = [];
        foreach($array as $value) {
            if(is_array($value)) {
                $values[] = $this->implodeNestedArray($value);
            } else {
                $values[] = $value;
            }
        }
        return implode("|", $values);
    }


    private function export($data, $form, $user)
    {
        $submit_components = [
            "InputElement",
            "Checkbox",
            "SelectElement",
            "SelectReferenceElement",
        ];
        $export = [];
        $headers = ["id","owner_type", "owner_id"];
        foreach($data as $data_row) {
            if($data_row["owner_type"]=="App\\Models\\User") {
                $owner_type = "User";
            } else if($data_row["owner_type"]=="App\\Models\\Group") {
                $owner_type = "Group";
            }
            $export_line = [
                "id"=>$data_row["id"],
                "owner_type"=>$owner_type,
                "owner_id"=>$data_row["owner_id"],
            ];
            if($data_row->has("owner")) {
                foreach($data_row->owner->toArray() as $key=>$value) {
                    if(!in_array("owner.{$key}",$headers)) {
                        $headers[] = "owner.{$key}";
                    }
                    if(is_array($value)) {
                        $export_line["owner.{$key}"] = str_replace(",", " ", $this->implodeNestedArray($value));
                    } else {
                        $export_line["owner.{$key}"] = str_replace(",", " ", $value);
                    }
                }
            }
            foreach($data_row["form_elements"] as $form_element) {
                if(!in_array("{$form_element['data']['label']}",$headers)) {
                    $headers[] = "{$form_element['data']['label']}";
                }
                if($form_element->component=="FileUploadElement") {
                    $fnames = [];
                    foreach($form_element["pivot"]["data"] as $file) {
                        $fnames[] = $file->name;
                    }
                    $export_line["{$form_element['data']['label']}"] = implode("|",$fnames);
                } else {
                    $export_line["{$form_element['data']['label']}"] = $form_element["pivot"]["data"];
                }
            }
            array_push($export, $export_line);
        }
        // $export_string = $this->tabDelimited($export);
        $export_string = $this->commaDelimited($export, $headers);
        $filepath = "D:\inetpub\MPortal\dfiles\submissions\\";
        $uniqId = substr(md5(uniqid(rand(), true)), 0, 6);
        $date = date('Y_m_d');
        $filename = $date."_".$form["id"]."_".$user->id.".csv";
        $file = $filepath.$filename;
        file_put_contents($file, $export_string);
        return $filename;
    }

    private function commaDelimited($data, $headers) {
        
        $string = "";
        $flag = false;
        foreach($data as $row) {
          if(!$flag) {
            $string .= implode(",", $headers) . "\r\n";
            $flag = true;
          }
          $rowTemplate = [];
          foreach($headers as $header) {
            if(array_key_exists($header, $row)) {
                $rowTemplate[] = $row[$header];
            } else {
                $rowTemplate[] = '';
            }
          }
          array_walk($rowTemplate, __NAMESPACE__ . '\cleanData');
          $string .= implode(",", $rowTemplate) . "\r\n";
        }
        return $string;
    }
    
    private function tabDelimited($data) {
        $string = "";
        $flag = false;
        foreach($data as $row) {
          if(!$flag) {
            $string .= implode("\t", array_keys($row)) . "\r\n";
            $flag = true;
          }
          array_walk($row, __NAMESPACE__ . '\cleanData');
          $string .= implode("\t", array_values($row)) . "\r\n";
        }
        return $string;
    }

}
function cleanData(&$str)
{
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}