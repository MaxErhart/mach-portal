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

use \Datetime;



class SubmissionControllerNoLogin extends Controller
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
        "date" => [
            "regex" => '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/',
            "error_message" => "Wrong date input"            
        ],              
    ];

    public function index(Request $request)
    {

        return response('Not found.', 404);
    }

    public function custom_validate($request, $form_elements) {
        foreach($form_elements as $form_element) {
            if($form_element->component=="InputElement") {
                $v = $request->get("{$form_element->id}");
                // return response()->json($v);
                if($form_element->data["required"]) {
                    if($v==null) {
                        abort(500, "Input {$form_element->data['label']} is required");
                    }
                }
                if(!preg_match($this->settings[$form_element->data["type"]]["regex"], $v)) {
                    
                    abort(500, $this->settings[$form_element->data["type"]]["error_message"]);
                }
            } else if($form_element->component=="Checkbox") {
                $v = $request->get("{$form_element->id}");
                if($form_element->data["required"]) {
                    if($v==null) {
                        abort(500, "Input {$form_element->data['label']} is required");
                    }
                }
            }else if($form_element->component=="FileUploadElement") {
                if($form_element->data["required"]) {
                    $v = $request->file($form_element->id);
                    if($v==null) {
                        abort(500, "Input {$form_element->data['label']} is required");
                    }                    
                }
            } else if($form_element->component=="FileUploadElement") {
                if($form_element->data["required"]) {
                    $v = $request->get("{$form_element->id}");
                    if($v==null) {
                        abort(500, "Input {$form_element->data['label']} is required");
                    }                    
                }
            } else {
                continue;
            }          
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            "form_id"=>"Required|integer",
        ]);
        $form = Form::findOrFail($request->get("form_id"));
        if($form->deadline && $form->deadline<new DateTime()) {
            abort(500, "Deadline passed.");
        }

        $form_elements = $form->form_elements;
        $this->custom_validate($request, $form_elements);

        $data = [];
        $content = "";
        $content .= "Vielen Dank für Ihre Anmeldung.<br>";
        $content .= "<br>Sie haben das Formular: {$form->name} ausgefüllt.";
        $content .=  "<br>Folgende Daten wurden übermittelt:";
        $email = "";
        $email_content_elements = [];
        foreach($form_elements as $form_element) {
            if($form_element->component=="InputElement" || $form_element->component=="SelectElement" || $form_element->component=="SelectReferenceElement") {
                $data[$form_element->id] = ["data"=>json_encode($request->get($form_element->id))];
                $email_content_elements[$form_element->position] = "<br>{$form_element->data['label']}: {$request->get($form_element->id)}";
            }            
            if($form_element->component=="FileUploadElement") {
                $path = $request->file($form_element->id)->store("file_submissions_form_{$form->name}");
                $data[$form_element->id] = ["data"=>json_encode($path)];
                $email_content_elements[$form_element->position] = "<br>{$form_element->data['label']}: {$path}";
            }
            if($form_element->component=="Checkbox") {
                if($request->get($form_element->id)==="true" || $request->get($form_element->id)==="1" || $request->get($form_element->id)===1 || $request->get($form_element->id)===true) {
                    $data[$form_element->id] = ["data"=>json_encode(true)];
                $email_content_elements[$form_element->position] = "<br>{$form_element->data['label']}: true";
                } else {
                    $data[$form_element->id] = ["data"=>json_encode(false)];
                $email_content_elements[$form_element->position] = "<br>{$form_element->data['label']}: false";
                }
            }
            if($form_element->id==$form->email_element_id) {
                $email = $request->get($form_element->id);
            }  
        }
        ksort($email_content_elements);
        foreach($email_content_elements as $key=>$element) {
            $content .=$element;
        }
        $content .= '<br>Bei Fragen wenden Sie sich bitte an <a href="mailto:portal@mach.kit.edu">portal@mach.kit.edu</a>.';
        $content .= '<br><br>Freundliche Grüße';
        $content .= '<br>Das Dekanat';

        $newSubmission = new Submission();
        $newSubmission->user()->associate(User::findOrFail(4));
        $newSubmission->form()->associate($form);
        $newSubmission->save();
        $newSubmission->form_elements()->sync($data);
        $newSubmission->form_elements->map(function($element, $content) {
            return $element->pivot->data = json_decode($element->pivot->data);
        });

        $header = "Mime-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=utf-8";
        $subject = "Automatische Antwort auf Ihre Anmeldung im MACH-Portal";
        mail($email, $subject, $content, $header);
        return response()->json($newSubmission);
    }

    public function update(Request $request, $id)
    {       

        return response()->json($submissions);

    }


    public function destroy($id)
    {
        return response()->json($submissions);

    }    
}