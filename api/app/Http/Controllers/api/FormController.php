<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormElement;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\User;
use App\Models\Group;
use App\Models\Email;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\URL;
use \Datetime;
class FormController extends Controller
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

    private function _checkFormMultipleSubmissions($form,$owner) {
        if(!$form->no_login && $form->multiple_submissions==0 && count($owner->submissions()->where('form_id', $form->id)->get()->toArray())>0) {
            $validation = [
                "type"=>1,
                "message"=>"Multiple submissions not allowed",
            ];
            abort(response()->json($validation, 401));
        }   
    }

    

    private function _checkFormDeadline($form,$user) {
        if($user && ($user->isAdmin() || $user->id!==$form->creator_id)) {
            return;
        }
        $today = new DateTime();
        $deadline = new DateTime($form->deadline);
        $deadline->modify('+1 day');
        if($form->deadline && $deadline<$today) {
            $validation = [
                "type"=>1,
                "message"=>"Deadline passed",
            ];
            abort(response()->json($validation, 401));
        }
    }
    
    private function _validateInputElement($element, $value) {
        if($element->data["required"] && ($value==null || $value=='')) {
            return [
                'status'=>400,
                'message'=>"Field required",
                'id'=>$element->id,
            ];
        }
        if(!preg_match($this->settings[$element->data["type"]]["regex"], $value)) {
            return [
                'status'=>400,
                'message'=>$this->settings[$element->data["type"]]["error_message"],
                'id'=>$element->id,
            ];
        }
        return null;
    }

    private function _validateFileUploadElement($element, $files) {
        if($element->data["required"] && $files==null) {
            return [
                'status'=>400,
                'message'=>"Field required",
                'id'=>$element->id,
            ];
        }
        return null;
    }

    private function _validateCheckboxElement($element, $value) {
        if($element->data["required"] && !$value) {
            return [
                'status'=>400,
                'message'=>"Field required",
                'id'=>$element->id,
            ];
        }
        return null;
    }

    private function _validateSelectElement($element, $value) {
        if($element->data["required"] && ($value==null || $value=='' || $value=='NaN')) {
            return [
                'status'=>400,
                'message'=>"Field required",
                'id'=>$element->id,
            ];
        }
        return null;
    }

    private function _validateSelectReferenceElement($element, $value) {
        if($element->data["required"] && ($value==null || $value=='' || $value=='NaN')) {
            return [
                'status'=>400,
                'message'=>"Field required",
                'id'=>$element->id,
            ];
        }
        return null;
    }

    private function _customValidate($request, $elements) {
        $validation = [
            "type"=>0,
            "elements"=>[],
            "message"=>"Input error",
        ];
        $abort = false;
        foreach($elements as $element) {
            if(!$element->input) {
                continue;
            }
            $error = null;
            if($element->component=="InputElement") {
                $error = $this->_validateInputElement($element, $request->get($element->id));
            } else if($element->component=="FileUploadElement") {
                $error = $this->_validateFileUploadElement($element, $request->file($element->id));
            } else if($element->component=="Checkbox") {
                $error = $this->_validateCheckboxElement($element, $request->boolean($element->id));
            } else if($element->component=="SelectElement") {
                $error = $this->_validateSelectElement($element, $request->get($element->id));
            } else if($element->component=="SelectReferenceElement") {
                $error = $this->_validateSelectReferenceElement($element, $request->get($element->id));
            }
            if($abort || $error!=null) {
                $abort = true;
                $validation['elements'][] = $error;
            }
        }
        if($abort) {
            abort(response()->json($validation, 400));
        }
    }

    private function _transformDataForStorage($request, $elements) {
        $data = [];
        foreach($elements as $element) {
            if(!$element->input) {
                continue;
            }
            if($element->component=="InputElement") {
                $value = $request->get($element->id);
                if($element->data["type"]=="data") {
                    $value = new DateTime($value);
                    $value = $value->format('Y-m-d');
                } else if($element->data["type"]=="number"||$element->data["type"]=="float"||$element->data["type"]=="integer") {
                    $value = floatval($value);
                }
            } else if($element->component=="FileUploadElement") {
                $files = $request->file($element->id);
                $value = [];
                foreach($files as $file) {
                    $path = $file->store("submission_files/{$element->form_id}", 'dfiles');
                    $url = URL::signedRoute('file_hosting',['fragment'=>$path]);
                    $value[] = ["url"=>$url,'disk'=>'dfiles','fragment'=>$path,"name"=>$file->getClientOriginalName()];
                }
                // $value = json_decode($value);

            } else if($element->component=="Checkbox") {
                $value = $request->boolean($element->id);
            } else if($element->component=="SelectElement") {
                $value = $request->get($element->id);
            } else if($element->component=="SelectReferenceElement") {
                $value = $request->get($element->id);
            } else if($element->component=="DoubleInputElement") {
              // $input_values = json_decode($request->get($element->id),true);
              $value = json_decode($request->get($element->id),true);
              // $value = '<a href="'.$input_values['url'].'">'.$input_values['alias'].'</a>';
            }
            $data[$element->id] = $value;
        }
        return $data;
    }

    private function _getFormSubmission($user, $submission_id, $min_permission=1) {
        $submission = Submission::findOrFail($submission_id);
        $permission = $submission->getPermission($user);
        if($permission<$min_permission) {
            abort(response()->json([
                "message"=>"Unauthorized",
            ], 401));
        }
        $submission->permission = $permission;
        return $submission;
    }

    public function deleteFormSubmission($form_id, $submission_id) {
        $user = Auth::user();
        $submission = $this->_getFormSubmission($user, $submission_id, 3);
        if($submission->is_archived) {
            $min_form_permission = 3;
        } else {
            $min_form_permission = 1;
        }
        $form = $this->_getForm($form_id, $user, $min_permission=$min_form_permission);
        $this->_checkFormDeadline($form,$user);
        $submission->delete();
        return response()->json(null);
    }

    public function updateFormSubmission(Request $request, $form_id, $submission_id) {
        $user = Auth::user();
        $submission = $this->_getFormSubmission($user, $submission_id, 2);
        if($submission->is_archived) {
            $min_form_permission = 3;
        } else {
            $min_form_permission = 1;
        }
        $form = $this->_getForm($form_id, $user, $min_permission=$min_form_permission);
        // $this->_checkFormMultipleSubmissions($form,$user);
        $this->_checkFormDeadline($form,$user);
        
        $this->_customValidate($request, $form->form_elements[$form_id]);
        
        $data = $this->_transformDataForStorage($request, $form->form_elements[$form_id]);

        
        $submission->_data = $data;
        $owner = json_decode($request->get('submission_owner'),true);
        if(!empty($owner)) {
            if($owner['type']==="App\\Models\\User") {
                $owner = User::findOrFail($owner["id"]);
                if($owner->getUserPermissionOnUser($user)<3) {
                    $owner = $submission->owner;
                }
            } else {
                $owner = Group::findOrFail($owner["id"]);
                if($owner->getUserPermissionOnGroup($user)<3) {
                    $owner = $submission->owner;
                }
            }
            $submission->owner()->associate($owner);
        }
        unset($submission->permission);
        $submission->save();
        $submission->replies;
        $permission = $submission->getPermission($user);
        $submission->permission = $permission;
        $submission->owner;
        return response()->json(["form"=>$form, "submission"=>$submission]);
    }

    public function getFormSubmission($form_id, $submission_id) {
        $user = Auth::user();
        $form = $this->_getForm($form_id, $user);
        $form_elements = $form->getFormElementsIncludingReferencesRecursively();
        $form = collect($form);
        $form['form_elements'] = $form_elements;
        $submission = $this->_getFormSubmission($user, $submission_id, 2);
        return response()->json(['form'=>$form,'submission'=>$submission]);
    }


    public function confirmAnonFormSubmission(Request $request) {
        if(!$request->hasValidSignature()) {
            abort(403,"Invalid token link may be expired");
        }
        $submission = Submission::where('confirmation_id', $request->id)->get()[0];
        if($submission->confirmed) {
            abort(403, "Submission is already confirmed");
        }
        $submission->confirmed = true;
        $submission->save();

        $success = '<!DOCTYPE html>
        <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
        
                <title>Success</title>
        
                <!-- Fonts -->
                <link rel="preconnect" href="https://fonts.gstatic.com">
                <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
        
                <style>
                    /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}code{font-family:monospace,monospace;font-size:1em}[hidden]{display:none}html{font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;line-height:1.5}*,:after,:before{box-sizing:border-box;border:0 solid #e2e8f0}a{color:inherit;text-decoration:inherit}code{font-family:Menlo,Monaco,Consolas,Liberation Mono,Courier New,monospace}svg,video{display:block;vertical-align:middle}video{max-width:100%;height:auto}.bg-white{--bg-opacity:1;background-color:#fff;background-color:rgba(255,255,255,var(--bg-opacity))}.bg-gray-100{--bg-opacity:1;background-color:#f7fafc;background-color:rgba(247,250,252,var(--bg-opacity))}.border-gray-200{--border-opacity:1;border-color:#edf2f7;border-color:rgba(237,242,247,var(--border-opacity))}.border-gray-400{--border-opacity:1;border-color:#cbd5e0;border-color:rgba(203,213,224,var(--border-opacity))}.border-t{border-top-width:1px}.border-r{border-right-width:1px}.flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.justify-center{justify-content:center}.font-semibold{font-weight:600}.h-5{height:1.25rem}.h-8{height:2rem}.h-16{height:4rem}.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.leading-7{line-height:1.75rem}.mx-auto{margin-left:auto;margin-right:auto}.ml-1{margin-left:.25rem}.mt-2{margin-top:.5rem}.mr-2{margin-right:.5rem}.ml-2{margin-left:.5rem}.mt-4{margin-top:1rem}.ml-4{margin-left:1rem}.mt-8{margin-top:2rem}.ml-12{margin-left:3rem}.-mt-px{margin-top:-1px}.max-w-xl{max-width:36rem}.max-w-6xl{max-width:72rem}.min-h-screen{min-height:100vh}.overflow-hidden{overflow:hidden}.p-6{padding:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-4{padding-left:1rem;padding-right:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.pt-8{padding-top:2rem}.fixed{position:fixed}.relative{position:relative}.top-0{top:0}.right-0{right:0}.shadow{box-shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06)}.text-center{text-align:center}.text-gray-200{--text-opacity:1;color:#edf2f7;color:rgba(237,242,247,var(--text-opacity))}.text-gray-300{--text-opacity:1;color:#e2e8f0;color:rgba(226,232,240,var(--text-opacity))}.text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.text-gray-500{--text-opacity:1;color:#a0aec0;color:rgba(160,174,192,var(--text-opacity))}.text-gray-600{--text-opacity:1;color:#718096;color:rgba(113,128,150,var(--text-opacity))}.text-gray-700{--text-opacity:1;color:#4a5568;color:rgba(74,85,104,var(--text-opacity))}.text-gray-900{--text-opacity:1;color:#1a202c;color:rgba(26,32,44,var(--text-opacity))}.uppercase{text-transform:uppercase}.underline{text-decoration:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.tracking-wider{letter-spacing:.05em}.w-5{width:1.25rem}.w-8{width:2rem}.w-auto{width:auto}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}@-webkit-keyframes spin{0%{transform:rotate(0deg)}to{transform:rotate(1turn)}}@keyframes  spin{0%{transform:rotate(0deg)}to{transform:rotate(1turn)}}@-webkit-keyframes ping{0%{transform:scale(1);opacity:1}75%,to{transform:scale(2);opacity:0}}@keyframes  ping{0%{transform:scale(1);opacity:1}75%,to{transform:scale(2);opacity:0}}@-webkit-keyframes pulse{0%,to{opacity:1}50%{opacity:.5}}@keyframes  pulse{0%,to{opacity:1}50%{opacity:.5}}@-webkit-keyframes bounce{0%,to{transform:translateY(-25%);-webkit-animation-timing-function:cubic-bezier(.8,0,1,1);animation-timing-function:cubic-bezier(.8,0,1,1)}50%{transform:translateY(0);-webkit-animation-timing-function:cubic-bezier(0,0,.2,1);animation-timing-function:cubic-bezier(0,0,.2,1)}}@keyframes  bounce{0%,to{transform:translateY(-25%);-webkit-animation-timing-function:cubic-bezier(.8,0,1,1);animation-timing-function:cubic-bezier(.8,0,1,1)}50%{transform:translateY(0);-webkit-animation-timing-function:cubic-bezier(0,0,.2,1);animation-timing-function:cubic-bezier(0,0,.2,1)}}@media (min-width:640px){.sm\:rounded-lg{border-radius:.5rem}.sm\:block{display:block}.sm\:items-center{align-items:center}.sm\:justify-start{justify-content:flex-start}.sm\:justify-between{justify-content:space-between}.sm\:h-20{height:5rem}.sm\:ml-0{margin-left:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width:768px){.md\:border-t-0{border-top-width:0}.md\:border-l{border-left-width:1px}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}@media (prefers-color-scheme:dark){.dark\:bg-gray-800{--bg-opacity:1;background-color:#2d3748;background-color:rgba(45,55,72,var(--bg-opacity))}.dark\:bg-gray-900{--bg-opacity:1;background-color:#1a202c;background-color:rgba(26,32,44,var(--bg-opacity))}.dark\:border-gray-700{--border-opacity:1;border-color:#4a5568;border-color:rgba(74,85,104,var(--border-opacity))}.dark\:text-white{--text-opacity:1;color:#fff;color:rgba(255,255,255,var(--text-opacity))}.dark\:text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}}
                </style>
        
                <style>
                    body {
                        font-family: \'Nunito\', sans-serif;
                    }
                </style>
            </head>
            <body class="antialiased">
                <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
                    <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
                        <div class="flex items-center pt-8 sm:justify-start sm:pt-0">
                            <div class="px-4 text-lg text-gray-500 border-r border-gray-400 tracking-wider">
                                SUCCESS!                    </div>
        
                            <div class="ml-4 text-lg text-gray-500 uppercase tracking-wider">
                                YOUR SUBMISSION IS CONFIRMED                    </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>';

        return $success;
    
    }

    public function postAnonFormSubmission(Request $request, $form_id) {
        $form = $this->_getForm($form_id, null, $min_permission=2);
        $this->_checkFormDeadline($form,null);
        $this->_customValidate($request, $form->form_elements[$form_id]);
        $data = $this->_transformDataForStorage($request, $form->form_elements[$form_id]);

        $submission = new Submission([
            "_data"=>$data,
        ]);
        $owner = Group::findOrFail(131);
        $submission->owner()->associate($owner);
        $submission->form()->associate($form);
        $submission->save();
        $confirmation_id = $submission->id.uniqid();
        $submission->confirmation_id = $confirmation_id;
        $submission->save();

        $submission->replies;
        $permission = 2;
        $submission->permission = $permission;

        // {$form->name}
        $subject = "MACH-Portal Confirm Your Submission - Action Required";
        $body = '<!DOCTYPE html><html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en"><head><title></title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><!--[if mso]><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch><o:AllowPNG/></o:OfficeDocumentSettings></xml><![endif]--><style>
        *{box-sizing:border-box}body{margin:0;padding:0}a[x-apple-data-detectors]{color:inherit!important;text-decoration:inherit!important}#MessageViewBody a{color:inherit;text-decoration:none}p{line-height:inherit}.desktop_hide,.desktop_hide table{mso-hide:all;display:none;max-height:0;overflow:hidden}.image_block img+div{display:none} @media (max-width:520px){.row-content{width:100%!important}.mobile_hide{display:none}.stack .column{width:100%;display:block}.mobile_hide{min-height:0;max-height:0;max-width:0;overflow:hidden;font-size:0}.desktop_hide,.desktop_hide table{display:table!important;max-height:none!important}}
        </style></head><body style="background-color:#fff;margin:0;padding:0;-webkit-text-size-adjust:none;text-size-adjust:none"><table class="nl-container" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;background-color:#fff"><tbody><tr><td><table class="row row-1" align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0"><tbody><tr><td><table 
        class="row-content stack" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;border-radius:0;color:#000;width:500px" width="500"><tbody><tr><td class="column column-1" width="100%" style="mso-table-lspace:0;mso-table-rspace:0;font-weight:400;text-align:left;padding-bottom:5px;padding-top:5px;vertical-align:top;border-top:0;border-right:0;border-bottom:0;border-left:0"><table class="text_block block-1" width="100%" 
        border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word"><tr><td class="pad"><div style="font-family:sans-serif"><div class style="font-size:14px;font-family:Arial,\'Helvetica Neue\',Helvetica,sans-serif;mso-line-height-alt:16.8px;color:#00876c;line-height:1.2"><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px"><span style="font-size:20px;"><strong>MACH-Portal</strong></span></p></div></div></td></tr></table>
        <table class="text_block block-2" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word"><tr><td class="pad"><div style="font-family:sans-serif"><div class style="font-size:14px;font-family:Arial,\'Helvetica Neue\',Helvetica,sans-serif;mso-line-height-alt:16.8px;color:#555;line-height:1.2"><p style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">Dear User,</p><p 
        style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">&nbsp;</p><p style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">We have received an anonymous submission of the form:</p><p style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px"><a href="%anon_form_url%" target="_blank" style="text-decoration: underline; color: #0068A5;" rel="noopener">%anon_form_name%</a></p><p 
        style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">from your mail address.</p><p style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">&nbsp;</p><p style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">To ensure the accuracy of your submitted data, we kindly request your confirmation. Unconfirmed submissions will not be considered and are removed from the system.</p><p 
        style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">&nbsp;</p><p style="margin:0;font-size:14px;text-align:left;mso-line-height-alt:16.8px">To confirm your submission, please click on the following button:</p></div></div></td></tr></table><table class="button_block block-3" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0"><tr><td class="pad"><div class="alignment" align="center">
        <!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="%confirmation_link%" style="height:38px;width:89px;v-text-anchor:middle;" arcsize="0%" stroke="false" fillcolor="#00876c"><w:anchorlock/><v:textbox inset="0px,0px,0px,0px"><center style="color:#ffffff; font-family:Arial, sans-serif; font-size:14px"><![endif]-->
        <a href="%confirmation_link%" target="_blank" style="text-decoration:none;display:inline-block;color:#ffffff;background-color:#00876c;border-radius:0px;width:auto;border-top:0px solid transparent;font-weight:400;border-right:0px solid transparent;border-bottom:0px solid transparent;border-left:0px solid transparent;padding-top:5px;padding-bottom:5px;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;font-size:14px;text-align:center;mso-border-alt:none;word-break:keep-all;"><span style="padding-left:20px;padding-right:20px;font-size:14px;display:inline-block;letter-spacing:normal;"><span style="word-break: break-word; line-height: 28px;">Confirm</span></span></a>
        <!--[if mso]></center></v:textbox></v:roundrect><![endif]--></div></td></tr></table><table class="text_block block-4" width="100%" border="0" cellpadding="10" cellspacing="0" role="presentation" style="mso-table-lspace:0;mso-table-rspace:0;word-break:break-word"><tr><td class="pad"><div style="font-family:sans-serif"><div class style="font-size:14px;font-family:Arial,\'Helvetica Neue\',Helvetica,sans-serif;mso-line-height-alt:16.8px;color:#555;line-height:1.2"><p 
        style="margin:0;font-size:14px;mso-line-height-alt:16.8px">Please note that the confirmation link will expire after 60 minutes, so we encourage you to click on it as soon as possible.</p><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px">&nbsp;</p><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px">
        If you did not initiate this submission or have any concerns regarding your information please reach out to us at <a href="mailto:%company_address%" target="_blank" rel="noopener" style="text-decoration: underline; color: #0068A5;">%company_address%</a>.</p><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px">&nbsp;</p><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px">Best regards,</p><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px">
        &nbsp;</p><p style="margin:0;font-size:14px;mso-line-height-alt:16.8px">Mach-Portal</p></div></div></td></tr></table></td></tr></tbody></table></td></tr></tbody></table></td></tr></tbody></table></body></html>';
        
        $url = URL::temporarySignedRoute("anon_confirm", now()->addMinutes(60), ['id'=>$confirmation_id]);

        $placeholders = [
            "%anon_form_url%"=>"https://www-3.mach.kit.edu/dist/#/public/form/{$form->id}",
            "%anon_form_name%"=>$form->name,
            "%confirmation_link%"=>$url,
            "%company_address%"=>"portal@macht.kit.edu",
        ];

        $body = $this->_replacePlaceholders($body,$placeholders);

        $email = new Email([
            "subject"=>$subject,
            "body"=>$body,
            "to_addresses"=>[$request->get($form->email_element_id)],
            "from_address"=>"portal@mach.kit.edu",
            "from_alias"=>"MACH-Portal",
        ]);
        $email->send();

        return response()->json(["form"=>$form, "submission"=>$submission]);
    }

    private function _replacePlaceholders($text, $placeholders) {
    
        foreach($placeholders as $placeholder=>$value) {
            $text = str_replace($placeholder, $value, $text);
        }

        return $text;

    }

    public function postFormSubmission(Request $request, $form_id) {
        $user = Auth::user();
        $form = $this->_getForm($form_id, $user, $min_permission=2);
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

        $this->_checkFormMultipleSubmissions($form,$owner);
        $this->_checkFormDeadline($form,$user);


        
        $this->_customValidate($request, $form->form_elements[$form->id]);
        
        $data = $this->_transformDataForStorage($request, $form->form_elements[$form->id]);

        $submission = new Submission([
            "_data"=>$data,
        ]);
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
        $submission->owner()->associate($owner);
        $submission->form()->associate($form);
        $submission->save();
        $submission->replies;
        $permission = $submission->getPermission($user);
        $submission->permission = $permission;

        return response()->json(["form"=>$form, "submission"=>$submission]);
    }

    public function getFormSubmissions($form_id, $permission=1,$private=false) {

        $user = Auth::user();
        $form = $this->_getForm($form_id, $user,$permission,$decorate=false);
        $form->permission = $form->getFormPermission($user);

        $on_groups = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
        $on_users = DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->get();
        $form->permissions = ["group"=>$on_groups,"user"=>$on_users];
        $form->form_bescheid_settings;
        $form->group_observers;
        $form->user_observers;
        $form->tags;

        $data = $form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user);
        $form = collect($form);
        $form['form_elements'] = $data[0];
        $form->forget('form_submissions');
        $submissions = $data[1];
        if($private) {
            return ['form'=>$form,'submissions'=>$submissions];
        }
        return response()->json(['form'=>$form,'submissions'=>$submissions]);
    }

    private function _getForm($form_id, $user, $min_permission=1,$decorate=true) {
        $form = Form::findOrFail($form_id);
        $permission = $form->getFormPermission($user);
        
        if($permission<$min_permission) {
            abort(response()->json([
                "message"=>"Unauthorized",
                "a"=>$permission,
            ], 401));
        }
        if($decorate) {
            $form->permission = $permission;
            $form->form_bescheid_settings;
            $form->group_observers;
            $form->user_observers;
            $form->form_elements = $form->getFormElementsIncludingReferencesRecursively();
            $permissions = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
            $permissions=$permissions->concat(DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->get());
            $form->permissions = $permissions;
            $form->tags;
        }
        return $form;
    }

    public function getForms() {
        $user = Auth::user();
        $forms = Form::all();
        foreach($forms as $key=>$form) {

            $permission = $form->getFormPermission($user);

            if($permission<=0) {
                $forms->forget($key);
                continue;
            }
            $form->permission = $permission;
            $form->group_observers;
            $form->user_observers;
            $permissions = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
            $permissions=$permissions->concat(DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->get());
            $form->permissions = $permissions;
            $form->form_bescheid_settings;
            $form->tags;
            $form->form_elements = $form->getFormElementsIncludingReferencesRecursively();

        }
        $forms = $forms->values();

        return response()->json($forms);
    }

    public function getForm(Request $request, $form_id) {
        $user = Auth::user();
        $form = $this->_getForm($form_id,$user);
        return response()->json($form);
    }

    public function deleteForm(Request $request, $form_id) {
        $reference_elements = FormElement::where(['component'=>'SelectReferenceElement'])->get();
        foreach($reference_elements as $element) {
            if($element->data['formId']==$form_id) {
                $form = Form::findOrFail($element->form_id);
                abort(response()->json([
                    "message"=>"Form is being referenced by another form with id: {$form->id} and name: {$form->name}",
                ], 403));
            }
        }
        $user = Auth::user();
        $form = $this->_getForm($form_id, $user, 4);
        DB::table('agent_group_submissions_permissions')->where('form_id',$form_id)->delete();
        DB::table('agent_user_submissions_permissions')->where('form_id',$form_id)->delete();
        DB::table('agent_form_permissions')->where('form_id',$form_id)->delete();
        DB::table('form_elements')->where('form_id',$form_id)->delete();
        $form->tags()->detach();
        $form->delete();
        return response()->json(null);
    }

    public function updateForm(Request $request, $form_id) {
        $request->validate([
            "name"=>"required|max:255",
            "deadline"=>"nullable|date",
            "elements"=>"required",
            "public"=>"required",
            "own_sub"=>"required|integer|between:0,3",
        ]);
        $elements = json_decode($request->get("elements"), true);
        $this->validateFormElements($elements);

        $user = Auth::user();
        $form = $this->_getForm($form_id, $user, 3,false);
        $form->name = $request->get("name");
        $form->deadline = $request->get("deadline");
        $form->multiple_submissions = $request->boolean("multiple_submissions");
        $form->public = $request->boolean("public");
        $form->display = $request->boolean("display");
        $form->submissions = (int) $request->get("own_sub");
        $form->save();


        $permissions = json_decode($request->get("submissionPermissions"), true);
        if($permissions) {
            $this->_setSubmissionPermissions($form,$permissions);
        }
        $tags = collect(json_decode($request->get("tags"),true))->pluck('id');
        $group_observers_view = json_decode($request->get("group_observers_view"), true);
        $group_observers_submit = json_decode($request->get("group_observers_submit"), true);
        $user_observers_view = json_decode($request->get("user_observers_view"), true);
        $user_observers_submit = json_decode($request->get("user_observers_submit"), true);
        $group_coauthors = json_decode($request->get("group_form_permission_edit"), true);
        $user_coauthors = json_decode($request->get("user_form_permission_edit"), true);
        $this->_setFormPermissions($form,$group_observers_view,$group_observers_submit,$user_observers_view,$user_observers_submit,$group_coauthors,$user_coauthors);

        $this->_setFormElements($form,$elements);
        $form->tags()->sync($tags);

        $form = $this->_getForm($form_id,$user);
        $form_elements = $form->getFormElementsIncludingReferencesRecursively();
        $form = collect($form);
        $form['form_elements'] = $form_elements;
        return response()->json($form);
    }

    public function _setFormElements($form,$elements) {
        $elements = collect($elements);
        $new_elements = $elements->filter(function($element) {
            return $element['id']===NULL;
        });
        $old_elements = $elements->filter(function($element) {
            return $element['id']!==NULL;
        });
        $update_elements = FormElement::where('form_id',$form->id)->whereIn('id', $old_elements->pluck('id'))->get();
        $new_db_element_ids = [];
        foreach($new_elements as $element) {
            $newElement = new FormElement([
                "component"=>$element["component"],
                "position"=>$element["position"],
                "show"=>filter_var($element["show"], FILTER_VALIDATE_BOOLEAN),
                "input"=>filter_var($element["input"], FILTER_VALIDATE_BOOLEAN),
                "data"=>$element["data"],
            ]);
            $form->base_form_elements()->save($newElement);
            $new_db_element_ids[] = $newElement->id;
        }
        foreach($update_elements as $element) {
            $element->update($old_elements->filter(function($old_element) use($element) {
                return $old_element['id']==$element->id;
            })->first());
            $element->save();
        }

        FormElement::where('form_id',$form->id)->whereNotIn('id', $old_elements->pluck('id'))->whereNotIn('id',$new_db_element_ids)->delete();

    }

    public function copy($id) {
        $user = Auth::user();
        $form = $this->_getForm($id,$user);

        $copy = new Form([
            "name"=>$form->name." - copy",
            "deadlilne"=>$form->deadline,
            "multiple_submissions"=>$form->multiple_submissions,
            "public"=>$form->public,
            "submissions"=>$form->submissions,
            "display"=>$form->display,            
        ]);
        $copy->creator()->associate(Auth::user())->save();

        $group_permissions=DB::table('agent_group_submissions_permissions')->where('form_id',$form->id)->get();
        $data = [];
        foreach($group_permissions as $group_permission) {
            $row = (array) $group_permission;
            unset($row['id']);
            $row['form_id'] = $copy->id;
            $data[] = $row;
        }
        DB::table('agent_group_submissions_permissions')->insert($data);

        $user_permissions=DB::table('agent_user_submissions_permissions')->where('form_id',$form->id)->get();
        $data = [];
        foreach($user_permissions as $user_permission) {
            $row = (array) $user_permission;
            unset($row['id']);
            $row['form_id'] = $copy->id;
            $data[] = $row;
        }
        DB::table('agent_user_submissions_permissions')->insert($data);
    
        $data = [];
        foreach($form->group_observers as $group_observer) {
            $row = $group_observer->pivot->toArray();
            $row['form_id'] = $copy->id;
            $data[] = $row;
        }
        foreach($form->user_observers as $user_observer) {
            $row = $user_observer->pivot->toArray();
            $row['form_id'] = $copy->id;
            $data[] = $row;
        }
        DB::table('agent_form_permissions')->insert($data);

        $data = [];
        foreach($form->base_form_elements as $form_element) {
            $row = $form_element->toArray();
            unset($row['id']);
            unset($row['created_at']);
            unset($row['updated_at']);
            $row['form_id'] = $copy->id;
            $row['data'] = json_encode($row['data']);
            $data[] = $row;
        }
        DB::table('form_elements')->insert($data);
        $copy->tags()->sync($form->tags);
        $copy->save();

        $copy = $this->_getForm($copy->id, $user);

        return response()->json($copy);
    }

    private function getWildcards($string_expression, $test_string) {

        $expressen_open_bracket_list = explode("{",$string_expression);
        $segments = [];
        foreach($expressen_open_bracket_list as $expressen_open_bracket_segment) {
            $segments = array_merge($segments,explode("}", $expressen_open_bracket_segment));
        }
        foreach($segments as $index=>$segment) {
            if($segment=="") {
                unset($segments[$index]);
            }
        }
        $segments = array_values($segments);
        $wildcards = [];
        $match = true;
        $string = $test_string;
        foreach($segments as $index=>$segment) {

            if(substr($segment,0,1)==="#") {

                if(count($segments)<=$index+1) {
                    $wildcards[$segment] = $string;
                    $string = "";
                    continue;
                }
                $split_string = explode($segments[$index+1], $string);
                if(count($split_string)===1) {
                    if($split_string[0]===$string) {
                        $match = false;
                        break;
                    }
                    if($split_string[0]==="") {
                        $wildcards[$segment] = "";
                        continue;
                    }
                    abort(response()->json($split_string,403));
                }

                $wildcards[$segment] = $split_string[0];
                unset($split_string[0]);
                $string = $segments[$index+1].join($split_string);

                continue;
            }
            if(substr($string,0,strlen($segment))==$segment) {
                $string = substr($string,strlen($segment));
            }
        }
        if($match && $string==="") {
            return $wildcards;
        } 
        return NULL;

    }

    public function wildcardToSubmissionSettings(Request $request) {
        $groups = json_decode($request->get("groups"),true);
        $wildcard_source = $request->get("wildcard_source");
        $wildcard_target = $request->get("wildcard_target");
        $wildcard_permission = $request->get("wildcard_permission");

        $wildcards_sources = [];
        $match_source = [];
        foreach($groups as $group) {
            if($wildcards = $this->getWildcards($wildcard_source, $group["name"])) {
                $wildcards_sources[$group["id"]] = $wildcards;
            }
            if($wildcard_source===$group["name"]) {
                $match_source[] = $group["id"];
            }
        }

        $wildcards_targets = [];
        $match_target = [];
        foreach($groups as $group) {
            if($wildcards = $this->getWildcards($wildcard_target, $group["name"])) {
                $wildcards_targets[$group["id"]] = $wildcards;
            }
            if($wildcard_target===$group["name"]) {
                $match_target[] = $group["id"];
            }
        }

        $settings = [];
        foreach($wildcards_sources as $source_group_id=>$wildcards_source) {
            foreach($wildcards_targets as $target_group_id=>$wildcards_target) {
                $is_good = true;
                foreach($wildcards_source as $wildcard_source=>$source_value) {
                    if($is_good && array_key_exists($wildcard_source,$wildcards_target) && $source_value!==$wildcards_target[$wildcard_source]) {
                        $is_good = false;
                    }
                }
                if($is_good) {
                    $settings[] = ["sourceType"=>"Group","source"=>$source_group_id,"targetType"=>"Group","target"=>$target_group_id,"type"=>$wildcard_permission,"id"=>NULL];
                }
    
            }
            foreach($match_target as $target_group_id) {
                $settings[] = ["sourceType"=>"Group","source"=>$source_group_id,"targetType"=>"Group","target"=>$target_group_id,"type"=>$wildcard_permission,"id"=>NULL];
            }
        }
        foreach($match_source as $source_group_id) {
            foreach($wildcards_targets as $target_group_id=>$wildcards_target) {
                $settings[] = ["sourceType"=>"Group","source"=>$source_group_id,"targetType"=>"Group","target"=>$target_group_id,"type"=>$wildcard_permission,"id"=>NULL];
    
            }
            foreach($match_target as $target_group_id) {
                $settings[] = ["sourceType"=>"Group","source"=>$source_group_id,"targetType"=>"Group","target"=>$target_group_id,"type"=>$wildcard_permission,"id"=>NULL];
            }
        }
        // if(count($wildcard_source)==0) {

        // }
        // if(count($wildcard_target)==0)


        return $settings;
    }

    private function validateFormElements($elements) {
        if(!$elements || count($elements)<=0) {
            abort(response()->json([
                "errors"=>[
                    "form_elements"=>[
                        "message"=>"No form elements are given."
                    ]
                ],
                "message"=>"The given data was invalid.",
            ],422));
        }
    }

    public function postForm(Request $request) {
        $request->validate([
            "name"=>"required|max:255",
            "deadline"=>"nullable|date",
            "elements"=>"required",
            "public"=>"required",
            "own_sub"=>"required|integer|between:0,3",
        ]);
        $elements = json_decode($request->get("elements"), true);
        $this->validateFormElements($elements);

        $user = Auth::user();
        $form = new Form([
            "name"=>$request->get("name"),
            "deadline"=>$request->get("deadline"),
            "multiple_submissions"=>$request->boolean("multiple_submissions"),
            "public"=>$request->boolean("public"),
            "display"=>$request->boolean("display"),
            "submissions"=>(int) $request->get("own_sub"),
        ]);
        $form->creator()->associate($user)->save();

        $permissions = json_decode($request->get("submissionPermissions"), true);
        if($permissions) {
            $this->_setSubmissionPermissions($form,$permissions);
        }

        $group_observers_view = json_decode($request->get("group_observers_view"), true);
        $group_observers_submit = json_decode($request->get("group_observers_submit"), true);
        $user_observers_view = json_decode($request->get("user_observers_view"), true);
        $user_observers_submit = json_decode($request->get("user_observers_submit"), true);
        $group_coauthors = json_decode($request->get("group_form_permission_edit"), true);
        $user_coauthors = json_decode($request->get("user_form_permission_edit"), true);
        $this->_setFormPermissions($form,$group_observers_view,$group_observers_submit,$user_observers_view,$user_observers_submit,$group_coauthors,$user_coauthors);

        $this->_setFormElements($form,$elements);
        $tags = collect(json_decode($request->get("tags"),true))->pluck('id');
        $form->tags()->sync($tags);


        $form->permission = 3;
        $form_elements = $form->getFormElementsIncludingReferencesRecursively();
        $form = collect($form);
        $form['form_elements'] = $form_elements;
        return response()->json($form);

    }

    private function _setSubmissionPermissions($form,$permissions) {

        $group_permissions=DB::table('agent_group_submissions_permissions')->where('form_id',$form->id)->get();
        $user_permissions=DB::table('agent_user_submissions_permissions')->where('form_id',$form->id)->get();

        $agent_on_user_insert = [];
        $agent_on_group_insert = [];
        foreach($permissions as $permission) {
            $sourceType = $permission["sourceType"];
            if($sourceType=='Group') {
                $sourceType = "App\\Models\\Group";
            } else {
                $sourceType = "App\\Models\\User";
            }
            $source = $permission["source"];
            $targetType = $permission["targetType"];
            $target = $permission["target"];
            $type = $permission["type"];
            if($targetType==="Group") {
                $permission_data = ["agent_id"=>$source,"agent_type"=>$sourceType,"group_id"=>$target,"form_id"=>$form->id,"permission"=>$type];
                if($permission['id']) {
                    $table_permission_key = $group_permissions->search(function($group_permission) use($permission) {
                        return $group_permission->id==$permission['id'];
                    });
                    $table_permission = $group_permissions->pull($table_permission_key);

                    if($table_permission_key!==NULL && $table_permission) {
                        $group_permissions->forget($table_permission_key);
                        DB::table('agent_group_submissions_permissions')->where('id',$permission['id'])->update($permission_data);
                    }
                } else {
                    $agent_on_group_insert[] = $permission_data;
                }
            } else {
                $permission_data = ["agent_id"=>$source,"agent_type"=>$sourceType,"user_id"=>$target,"form_id"=>$form->id,"permission"=>$type];

                if($permission['id']) {
                    $table_permission_key = $user_permissions->search(function($user_permission) use($permission) {
                        return $user_permission->id==$permission['id'];
                    });
                    $table_permission = $user_permissions->pull($table_permission_key);

                    if($table_permission_key!==NULL && $table_permission) {
                        $user_permissions->forget($table_permission_key);
                        DB::table('agent_user_submissions_permissions')->where('id',$permission['id'])->update($permission_data);
                    }
                } else {
                    $agent_on_user_insert[] = $permission_data;
                }

            }
        }
        DB::table('agent_group_submissions_permissions')->insert($agent_on_group_insert);
        DB::table('agent_user_submissions_permissions')->insert($agent_on_user_insert);
        DB::table('agent_group_submissions_permissions')->whereIn('id', $group_permissions->pluck('id'))->delete();
        DB::table('agent_user_submissions_permissions')->whereIn('id', $user_permissions->pluck('id'))->delete();
    }

    private function _setFormPermissions($form,$group_observers_view,$group_observers_submit,$user_observers_view,$user_observers_submit,$group_coauthors,$user_coauthors) {

        $current_form_permissions = DB::table('agent_form_permissions')->where('form_id',$form->id)->get();
        $current_form_permissions_groups_view = $current_form_permissions->filter(function ($permission) {
            return $permission->agent_type=='App\Models\Group' && $permission->permission==1;
        });
        $current_form_permissions_groups_submit = $current_form_permissions->filter(function ($permission) {
            return $permission->agent_type=='App\Models\Group' && $permission->permission==2;
        });
        $current_form_permissions_groups_edit = $current_form_permissions->filter(function ($permission) {
            return $permission->agent_type=='App\Models\Group' && $permission->permission>=3;
        });
        $current_form_permissions_users_view = $current_form_permissions->filter(function ($permission) {
            return $permission->agent_type=='App\Models\User' && $permission->permission==1;
        });
        $current_form_permissions_users_submit = $current_form_permissions->filter(function ($permission) {
            return $permission->agent_type=='App\Models\User' && $permission->permission==2;
        });  
        $current_form_permissions_users_edit = $current_form_permissions->filter(function ($permission) {
            return $permission->agent_type=='App\Models\User' && $permission->permission>=3;
        });
        $form_permissions = [];
        $history = [];
        if(!$form->public) {
            foreach($group_observers_view as $group_observer) {
                $key = $current_form_permissions_groups_view->search(function ($permission) use($group_observer){
                    return $permission->agent_id==$group_observer['id'];
                });
                $permission_data = ['agent_id'=>$group_observer['id'],'agent_type'=>"App\\Models\\Group",'form_id'=>$form->id,'permission'=>1];
                $form_permission = $current_form_permissions_groups_view->pull($key);
                if($key!==NULL && $form_permission) {
                    $current_form_permissions_groups_view->forget($key);
                    DB::table('agent_form_permissions')->where('id',$form_permission->id)->update($permission_data);
                } else {
                    $form_permissions[] = $permission_data;
                }
            }

            foreach($group_observers_submit as $group_observer) {
                $key = $current_form_permissions_groups_submit->search(function ($permission) use($group_observer){
                    return $permission->agent_id==$group_observer['id'];
                });

                $permission_data = ['agent_id'=>$group_observer['id'],'agent_type'=>"App\\Models\\Group",'form_id'=>$form->id,'permission'=>2];
                $form_permission = $current_form_permissions_groups_submit->pull($key);

                $history[] = [$key,$group_observer,$form_permission];
                if($key!==NULL && $form_permission) {

                    $current_form_permissions_groups_submit->forget($key);
                    DB::table('agent_form_permissions')->where('id',$form_permission->id)->update($permission_data);
                } else {
                    $form_permissions[] = $permission_data;
                }
            }

            foreach($user_observers_view as $user_observer) {
                $key = $current_form_permissions_users_view->search(function ($permission) use($user_observer){
                    return $permission->agent_id==$user_observer['id'];
                });
                $permission_data = ['agent_id'=>$user_observer['id'],'agent_type'=>"App\\Models\\User",'form_id'=>$form->id,'permission'=>1];
                $form_permission = $current_form_permissions_users_view->pull($key);
                if($key!==NULL && $form_permission) {
                    $current_form_permissions_users_view->forget($key);
                    DB::table('agent_form_permissions')->where('id',$form_permission->id)->update($permission_data);
                } else { 
                    $form_permissions[] = $permission_data;
                }
            }
            
            foreach($user_observers_submit as $user_observer) {
                $key = $current_form_permissions_users_submit->search(function ($permission) use($user_observer){
                    return $permission->agent_id==$user_observer['id'];
                });
                $permission_data = ['agent_id'=>$user_observer['id'],'agent_type'=>"App\\Models\\User",'form_id'=>$form->id,'permission'=>2];
                $form_permission = $current_form_permissions_users_submit->pull($key);
                if($key!==NULL && $form_permission) {
                    $current_form_permissions_users_submit->forget($key);
                    DB::table('agent_form_permissions')->where('id',$form_permission->id)->update($permission_data);
                } else { 
                    $form_permissions[] = $permission_data;
                }
            }

        }

        foreach($group_coauthors as $group_observer) {
            $key = $current_form_permissions_groups_edit->search(function ($permission) use($group_observer){
                return $permission->agent_id==$group_observer['id'];
            });
            $permission_data = ['agent_id'=>$group_observer['id'],'agent_type'=>"App\\Models\\Group",'form_id'=>$form->id,'permission'=>3];
            $form_permission = $current_form_permissions_groups_edit->pull($key);
            if($key!==NULL && $form_permission) {
                $current_form_permissions_groups_edit->forget($key);
                DB::table('agent_form_permissions')->where('id',$form_permission->id)->update($permission_data);
            } else { 
                $form_permissions[] = $permission_data;
            }
        }




        foreach($user_coauthors as $user_observer) {
            $key = $current_form_permissions_users_edit->search(function ($permission) use($user_observer){
                return $permission->agent_id==$user_observer['id'];
            });
            $permission_data = ['agent_id'=>$user_observer['id'],'agent_type'=>"App\\Models\\User",'form_id'=>$form->id,'permission'=>3];
            $form_permission = $current_form_permissions_users_edit->pull($key);
            if($key!==NULL && $form_permission) {
                $current_form_permissions_users_edit->forget($key);
                DB::table('agent_form_permissions')->where('id',$form_permission->id)->update($permission_data);
            } else { 
                $form_permissions[] = $permission_data;
            }
        }

        DB::table('agent_form_permissions')->whereIn('id', $current_form_permissions_groups_view->pluck('id'))->delete();
        DB::table('agent_form_permissions')->whereIn('id', $current_form_permissions_groups_submit->pluck('id'))->delete();
        DB::table('agent_form_permissions')->whereIn('id', $current_form_permissions_groups_edit->pluck('id'))->delete();
        DB::table('agent_form_permissions')->whereIn('id', $current_form_permissions_users_view->pluck('id'))->delete();
        DB::table('agent_form_permissions')->whereIn('id', $current_form_permissions_users_submit->pluck('id'))->delete();
        DB::table('agent_form_permissions')->whereIn('id', $current_form_permissions_users_edit->pluck('id'))->delete();
        DB::table('agent_form_permissions')->insert($form_permissions);
    }


    
    public function getArchiveSubmissions(Request $request, $form_id,$key=null) {
        $user = Auth::user();
        $form = $this->_getForm($form_id, $user,1,$decorate=false);
        $submissions = $form->getArchiveSubmissions($user,$key);
        return response()->json($submissions);
    }

    public function dearchiveSubmissions(Request $request) {
        $user = Auth::user();
        $ids = json_decode($request->get('ids'),true);
        $form_id = $request->get('form_id');
        $form = $this->_getForm($form_id, $user,3,false);
        $submissions = Submission::whereIn('id', $ids)->get();
        foreach($submissions as $sub_key=>$submission) {
            $submission->is_archived = 0;
            $submission->archive_owner = NULL;
            $submission->archive_owner_type = NULL;
            $submission->archive_data = NULL;
            $submission->archive_element_label = NULL;
            $submission->archive_label_fallback = NULL;
            $submission->save();
        }
        $all_remaining_archived_submissions = Submission::where('form_id',$form_id)->where('is_archived',true)->get();
        $archive_groups = [];
        foreach($all_remaining_archived_submissions as $submission) {
            if(!in_array($submission->archive_group,$archive_groups)) {
                $archive_groups[] = $submission->archive_group;
            }
        }
        $form->archive_groups = $archive_groups;
        $form->save();
        $live_submissions = $form->getFormSubmissions($user);
        return response()->json(["live"=>$live_submissions,"dearchived"=>$submissions,"archive_groups"=>$archive_groups]);
    }

    public function archiveSubmissions(Request $request) {
        $user = Auth::user();

        $ids = json_decode($request->get('ids'),true);
        $form_id = $request->get('form_id');
        $submissions = Submission::whereIn('id', $ids)->get();

        $form = $this->_getForm($form_id, $user,3,false);

        $data = $form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user);
        $form->form_elements = $data[0];
        $form_submissions = $data[1];
        $archive_group = $request->get('archive_group');
        if($archive_group===null) {
            $archive_group = "default";
        }

        foreach($submissions as $submission) {
            $submission->is_archived = 1;
            $archive_owner_type = "App\\Models\\Group";
            if($submission->owner_type==="App\\Models\\User") {
                $submission->owner->groups;
                $owner = $submission->owner;
                $archive_owner_type = "App\\Models\\User";
            } else {
                $owner = $submission->owner;
            }
            
            $owner->toArray();
            $data_resolved = $this->resolveReferences($form, $form_submissions, $submission);
                
            $submission->archive_group = $archive_group;
            $submission->archive_owner = $owner;
            $submission->archive_owner_type = $archive_owner_type;
            $submission->archive_data = $data_resolved[0];
            $submission->archive_element_label = $data_resolved[1];
            $submission->archive_element_hirarchy = $data_resolved[2];
            $submission->save();
        }


        $form->offsetUnset('form_elements');

        if(!is_array($form->archive_groups)) {
            $form->archive_groups = [$archive_group];

        } else {
            $archive_groups = [];
            foreach($form->archive_groups as $key=>$group) {
                $archive_submissions = $form->getArchiveSubmissions($user,$group);
                if($archive_submissions->count()>0) {
                    $archive_groups[] = $group;
                }
            }
            $archive_groups[] = $archive_group;
            $form->archive_groups = $archive_groups;
        }
        $archive_submissions = $form->getArchiveSubmissions($user,$archive_group);

        $form->save();

        return response()->json(["archive"=>$archive_submissions,"archived"=>$submissions,"archive_groups"=>$form->archive_groups,"archive_group"=>$archive_group]);
    }





    private function resolveReferences($form, $form_submissions, $submission,$hirarchy=[],$prefix='') {
        if($submission===null) {
            return [];
        }
        $form_id = $submission->form_id;
        $data = [];
        $el_label = [];
        foreach($form->form_elements[$form_id] as $id=>$element) {
            if(!$element->input) {
                continue;
            }
            $label = $prefix.$id;
            $hirarchy[$id] = [];
            if(!array_key_exists($id,$submission->_data)) {
                continue;
            }
            $value = $submission->_data[$id];
            if($value===NULL) {
                $data[$id] = NULL;
            }
            if($element->component==="SelectReferenceElement") {
                $ref_submission = $form_submissions[$element->data["formId"]]->first(function($item,$key) use($value) {
                    return $item->id==$value;
                });
                if(!$ref_submission) {
                    continue;
                }
                $resolved = $this->resolveReferences($form,$form_submissions,$ref_submission,$hirarchy[$id],$label.'.');
                $data += $resolved[0];
                $el_label += $resolved[1];
                continue;
            }
            if($element->component==="SelectElement") {
                foreach($element["data"]["data"] as $option) {
                    if($option["id"]==$value) {
                        $data[$id] = $option["name"];
                        break;
                    }
                }
                $el_label[$id] = $label;
                continue;
            }
            $data[$id] = $value;
            $el_label[$id] = $label;
        }
        return [$data,$el_label,$hirarchy];
    }


















































    public function show($id, Request $request)
    {
        $form = Form::findOrFail($id);
        $user = Auth::user();
        if(!Auth::check() && !$form->no_login) {
            abort(response()->json([
                "message"=>"Not logged in",
            ], 403));
        }
        
        if(!$form->no_login) {
            $form->submit_permission = $form->getSubmitPermission($user);
            $form->form_permission = $form->getFormPermission($user);
            if($form->submit_permission<=0 && $form->form_permission<=0) {
                abort(response()->json([
                    "message"=>"Unauthorized",
                ], 401));
            }
        }




        $form->form_elements = $form->form_elements()->orderBy('position', 'asc')->get();
        $form->tags;
        $form->user_observers;
        $form->group_observers;

        foreach($form->form_elements as $form_element) {
            if($form_element->component!=="SelectReferenceElement") {
                continue;
            }
            $reference_form = Form::where('id', $form_element->data['formId'])->first();
            $reference_form->form_elements;
            $form_element->reference_form = $reference_form;
        }

        $permissions = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
        $permissions=$permissions->concat(DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->get());
        $form->setAttribute("permissions", $permissions);
        $form->form_bescheid_settings;
        return response()->json($form);
    }

    public function index()
    {

        $user = Auth::user();

        $forms = Form::get();

        $tmp = [];
        foreach($forms as $key=>$form) {
            $form->submit_permission = $form->getSubmitPermission($user);
            $form->form_permission = $form->getFormPermission($user);
            if($form->submit_permission>0 || $form->form_permission>0) {
                $tmp[] = $form;
            }
        }
        $forms = collect($tmp);
        $count = 0;
        foreach($forms as $form) {
            $form->form_elements = $form->form_elements()->orderBy('position', 'asc')->get();
            $form->tags;
            $form->user_observers;
            $form->group_observers;
            $permissions = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
            $permissions=$permissions->concat(DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->get());
            $form->setAttribute("permissions", $permissions);

            foreach($form->form_elements as $form_element) {
                if($form_element->component!=="SelectReferenceElement") {
                    continue;
                }
                $reference_form = Form::where('id', $form_element->data['formId'])->first();
                $reference_form->form_elements;
                $form_element->reference_form = $reference_form;
            }

        }
        // return response()->json($count);

        return response()->json($forms);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name"=>"required|max:255",
            "deadline"=>"nullable|date",
            "multiple_submissions"=>"required|max:255",
            "elements"=>"required",
            // "tags"=>"nullable",
            "public"=>"required",
            "submissions"=>"required",
        ]);

        $form = new Form([
            "name"=>$request->get("name"),
            "deadline"=>$request->get("deadline"),
            "multiple_submissions"=>(bool) filter_var($request->get("multiple_submissions"), FILTER_VALIDATE_BOOLEAN),
            "public"=>(bool) filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN),
            "submissions"=>(int) $request->get("submissions"),
            "display"=>(bool) filter_var($request->get("display"), FILTER_VALIDATE_BOOLEAN),
        ]);
        $form->creator()->associate(Auth::user())->save();



        if($request->get("submissions")=="1") {
            $permissions = json_decode($request->get("submissionPermissions"), true);
            foreach($permissions as $permission) {
                $sourceType = $permission["sourceType"];
                $source = $permission["source"];
                $targetType = $permission["targetType"];
                $target = $permission["target"];
                $type = $permission["type"];
                if($sourceType==='Group') {
                    $source_group = Group::findOrFail($source);
                    if($targetType==='Group') {
                        $source_group->submission_permissions_groups()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);

                    } else {
                        $source_group->submission_permissions_users()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);
                    }
                } else {
                    $source_user = User::findOrFail($source);
                    if($targetType==='Group') {
                        $source_user->submission_permissions_groups()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);
                    } else {
                        $source_user->submission_permissions_users()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);
                    }
                }
            }
        }
        
        $tmp = [];
        if(!filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN)) {
            $group_observers = json_decode($request->get("group_observers_view"), true);
            foreach($group_observers as $group_observer) {
                $tmp[$group_observer["id"]] = ['submit_permission'=>1,'form_id'=>$form->id];
            }
            $group_observers = json_decode($request->get("group_observers_submit"), true);
            foreach($group_observers as $group_observer) {
                $tmp[$group_observer["id"]] = ['submit_permission'=>2,'form_id'=>$form->id];
            }
        }
        $group_coauthors = json_decode($request->get("group_form_permission_view"), true);
        // foreach($group_coauthors as $group_coauthor) {
        //     if(array_key_exists($group_coauthor["id"], $tmp)) {
        //         $tmp[$group_coauthor["id"]]['form_permission'] = 1;
        //     } else {
        //         $tmp[$group_coauthor["id"]] = ['form_permission'=>1,'form_id'=>$form->id];
        //     }
        // }
        $group_coauthors = json_decode($request->get("group_form_permission_edit"), true);
        foreach($group_coauthors as $group_coauthor) {
            if(array_key_exists($group_coauthor["id"], $tmp)) {
                $tmp[$group_coauthor["id"]]['form_permission'] = 2;
            } else {
                $tmp[$group_coauthor["id"]] = ['form_permission'=>2,'form_id'=>$form->id];
            }
        }
        $group_observers = $tmp;

        $tmp = [];
        if(!filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN)) {

            $user_observers = json_decode($request->get("user_observers_view"), true);
            foreach($user_observers as $user_observer) {
                $tmp[$user_observer["id"]] = ['submit_permission'=>1,'form_id'=>$form->id];
            }
            $user_observers = json_decode($request->get("user_observers_submit"), true);
            foreach($user_observers as $user_observer) {
                $tmp[$user_observer["id"]] = ['submit_permission'=>2,'form_id'=>$form->id];
            }
        }
        // $user_coauthors = json_decode($request->get("user_form_permission_view"), true);
        // foreach($user_coauthors as $user_coauthor) {
        //     if(array_key_exists($user_coauthor["id"], $tmp)) {
        //         $tmp[$user_coauthor["id"]]['form_permission'] = 1;
        //     } else {
        //         $tmp[$user_coauthor["id"]] = ['form_permission'=>1,'form_id'=>$form->id];
        //     }
        // }
        $user_coauthors = json_decode($request->get("user_form_permission_edit"), true);
        foreach($user_coauthors as $user_coauthor) {
            if(array_key_exists($user_coauthor["id"], $tmp)) {
                $tmp[$user_coauthor["id"]]['form_permission'] = 2;
            } else {
                $tmp[$user_coauthor["id"]] = ['form_permission'=>2,'form_id'=>$form->id];
            }
        }

        $user_observers = $tmp;
        
        $form->user_observers()->sync($user_observers);
        $form->group_observers()->sync($group_observers);
        $form->user_observers;
        $form->group_observers;            

        

        $elements = json_decode($request->get("elements"), true);
        $tags = json_decode($request->get("tags"), true);


        foreach($elements as $element) {
            $newElement = new FormElement([
                "component"=>$element["component"],
                "position"=>$element["position"],
                "show"=>filter_var($element["show"], FILTER_VALIDATE_BOOLEAN),
                "input"=>filter_var($element["input"], FILTER_VALIDATE_BOOLEAN),
                "data"=>$element["data"],
            ]);
            $form->form_elements()->save($newElement);
        }
        if($tags) {
            $tagIds = [];
            foreach($tags as $tag) {
                $tag = Tag::where("name", $tag)->get()[0];
                $tagIds[] = $tag->id;
            }

            $form->tags()->sync($tagIds);
        }
        foreach($form->form_elements as $form_element) {
            if($form_element->component!="SelectReferenceElement") {
                continue;
            }
            $submissions = Submission::where('form_id', $form_element->data["formId"])->get();
            foreach($submissions as $submission) {
                $submission->owner;
                $submission->form;
                $submission->form_elements->map(function($element) {
                    $element->pivot->data = json_decode($element->pivot->data);
                    return;
                });
            }
            $form_element->setAttribute("submissions", $submissions);
        }
        
        return $this->show($form->id, $request);
    }



    public function update(Request $request, $id)
    {       
        $form = Form::findOrFail($id);

        $request->validate([
            "name"=>"required|max:255",
            "deadline"=>"nullable|date",
            "multiple_submissions"=>"required|max:255",
            "elements"=>"required",
            "public"=>"required",
            "submissions"=>"required",
        ]);

        
        $form->name = $request->get("name");
        $form->deadline = $request->get("deadline");
        $form->multiple_submissions = filter_var($request->get("multiple_submissions"), FILTER_VALIDATE_BOOLEAN);
        $form->public = filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN);
        $form->submissions = $request->get("own_sub");
        $form->display = filter_var($request->get("display"), FILTER_VALIDATE_BOOLEAN);

        if($request->get("submissions")=="1") {
            $permissions = json_decode($request->get("submissionPermissions"), true);
            
            DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->delete();
            DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->delete();

            foreach($permissions as $permission) {
                $sourceType = $permission["sourceType"];
                $source = $permission["source"];
                $targetType = $permission["targetType"];
                $target = $permission["target"];
                $type = $permission["type"];
                if($sourceType==='Group') {
                    $source_group = Group::findOrFail($source);

                    if($targetType==='Group') {
                        $source_group->submission_permissions_groups()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);

                    } else {
                        $source_group->submission_permissions_users()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);
                    }
                } else {
                    $source_user = User::findOrFail($source);
                    if($targetType==='Group') {
                        $source_user->submission_permissions_groups()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);
                    } else {
                        $source_user->submission_permissions_users()->attach([$target=>['permission'=>$type,'form_id'=>$form->id]]);
                    }
                }


            }
        }

        

        $tmp = [];
        if(!filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN)) {
            $group_observers = json_decode($request->get("group_observers_view"), true);
            foreach($group_observers as $group_observer) {
                $tmp[$group_observer["id"]] = ['submit_permission'=>1,'form_id'=>$form->id];
            }
            $group_observers = json_decode($request->get("group_observers_submit"), true);
            foreach($group_observers as $group_observer) {
                $tmp[$group_observer["id"]] = ['submit_permission'=>2,'form_id'=>$form->id];
            }
        }
        // $group_coauthors = json_decode($request->get("group_form_permission_view"), true);
        // foreach($group_coauthors as $group_coauthor) {
        //     if(array_key_exists($group_coauthor["id"], $tmp)) {
        //         $tmp[$group_coauthor["id"]]['form_permission'] = 1;
        //     } else {
        //         $tmp[$group_coauthor["id"]] = ['form_permission'=>1,'form_id'=>$form->id];
        //     }
        // }
        $group_coauthors = json_decode($request->get("group_form_permission_edit"), true);
        foreach($group_coauthors as $group_coauthor) {
            if(array_key_exists($group_coauthor["id"], $tmp)) {
                $tmp[$group_coauthor["id"]]['form_permission'] = 2;
            } else {
                $tmp[$group_coauthor["id"]] = ['form_permission'=>2,'form_id'=>$form->id];
            }
        }
        $group_observers = $tmp;

        $tmp = [];
        if(!filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN)) {

            $user_observers = json_decode($request->get("user_observers_view"), true);
            foreach($user_observers as $user_observer) {
                $tmp[$user_observer["id"]] = ['submit_permission'=>1,'form_id'=>$form->id];
            }
            $user_observers = json_decode($request->get("user_observers_submit"), true);
            foreach($user_observers as $user_observer) {
                $tmp[$user_observer["id"]] = ['submit_permission'=>2,'form_id'=>$form->id];
            }
        }
        // $user_coauthors = json_decode($request->get("user_form_permission_view"), true);
        // foreach($user_coauthors as $user_coauthor) {
        //     if(array_key_exists($user_coauthor["id"], $tmp)) {
        //         $tmp[$user_coauthor["id"]]['form_permission'] = 1;
        //     } else {
        //         $tmp[$user_coauthor["id"]] = ['form_permission'=>1,'form_id'=>$form->id];
        //     }
        // }
        $user_coauthors = json_decode($request->get("user_form_permission_edit"), true);
        foreach($user_coauthors as $user_coauthor) {
            if(array_key_exists($user_coauthor["id"], $tmp)) {
                $tmp[$user_coauthor["id"]]['form_permission'] = 2;
            } else {
                $tmp[$user_coauthor["id"]] = ['form_permission'=>2,'form_id'=>$form->id];
            }
        }

        $user_observers = $tmp;
        
        $form->user_observers()->sync($user_observers);
        $form->group_observers()->sync($group_observers);
        $form->user_observers;
        $form->group_observers;



        $elements = json_decode($request->get("elements"), true);
        $elements = collect($elements);
        $del = $form->form_elements->pluck("id")->diff($elements->pluck("id"));
        $update = $form->form_elements->pluck("id")->intersect($elements->pluck("id"));
        $create = $elements->where("id", "");
        

        foreach($form->form_elements as $form_element) {
            if($del->contains($form_element->id)) {
                $form_element->delete();
            }
            if($update->contains($form_element->id)) {
                $submit_element = $elements->firstWhere("id", $form_element->id);
                $form_element->data = $submit_element["data"];
                $form_element->position = $submit_element["position"];
                $form_element->show = filter_var($submit_element["show"], FILTER_VALIDATE_BOOLEAN);
                $form_element->input = filter_var($submit_element["input"], FILTER_VALIDATE_BOOLEAN);
                $form_element->save();
            }
        }

        
        foreach($create as $submit_element) {
            $newElement = new FormElement([
                "component"=>$submit_element["component"],
                "position"=>$submit_element["position"],
                "show"=>filter_var($submit_element["show"], FILTER_VALIDATE_BOOLEAN),
                "input"=>filter_var($submit_element["input"], FILTER_VALIDATE_BOOLEAN),
                "data"=>$submit_element["data"],                
            ]);
            $form->form_elements()->save($newElement);
        }
        $form->save();
        foreach($form->form_elements as $form_element) {
            if($form_element->component!="SelectReferenceElement") {
                continue;
            }
            $submissions = Submission::where('form_id', $form_element->data["formId"])->get();
            foreach($submissions as $submission) {
                $submission->owner;
                $submission->form;
                $submission->form_elements->map(function($element) {
                    $element->pivot->data = json_decode($element->pivot->data);
                    return;
                });
            }
            $form_element->setAttribute("submissions", $submissions);
        }
        $user = Auth::user();
        $form->submit_permission = $form->getSubmitPermission($user);
        $form->form_permission = $form->getFormPermission($user);

        return $this->show($form->id, $request);
    }




    

    
    private function parseInputData($entry,$element,$form,$reference_elements) {


        if($element->component==="SelectElement") {
            foreach($element->data["data"] as $select_option) {
                if($select_option["name"]===$entry) {
                    return $select_option["id"];
                }
            }
        } else {
            return $entry;
        }
        // else if($element->component==="SelectElement") {
            
        // } else if($element->component==="SelectElement") {
            
        // } else if($element->component==="SelectElement") {
            
        // }
    }

    private function findReferencedSubmissionFromValues($row,$element,$form,$reference_elements,$submissions) {
        if($element->component!=="SelectReferenceElement") {
            return NULL;
        }
        $chains = $form->resolveReferences($element,$reference_elements);
        $matches = NULL;
        foreach($chains as $chain) {
            $new_chain = array_map(function ($chain_fragment) use ($reference_elements) {
                return $reference_elements[$chain_fragment]->data["label"];
            },$chain);
            $key = implode(".", $new_chain);
            if(array_key_exists($key,$row)) {
                $el = $reference_elements[$chain[count($chain)-1]];
                $matching_submissions = $submissions[$el->form_id]->filter(function ($submission) use($el,$row,$key) {
                    if($submission->_data[$el->id]===$row[$key]) {
                        return true;
                    }
                    return false;
                });
                if($matching_submissions->isEmpty()) {
                    continue;
                }


                $current_matches = $matching_submissions->pluck('id');
                for($i=count($chain)-2;$i>0;$i--) {
                    $el = $reference_elements[$chain[$i]];

                    $current_matches = $submissions[$el->form_id]->filter(function ($submission) use($el,$current_matches) {
                        
                        if($current_matches->contains($submission->_data[$el->id])) {
                            return true;
                        }
                        return false;
                    })->pluck('id');
                }

                if($matches===NULL) {
                    $matches = $current_matches;
                } else {
                    $matches = $matches->filter(function ($id) use($current_matches){
                        if($current_matches->contains($id)) {
                            return true;
                        }
                        return false;
                    });
                }
            }
        }
        if($matches!==NULL && count($matches)>0) {
            $matches = $matches->values()->all();
            return $matches[0];
        } else {
            return NULL;
        }
    }

    public function upload(Request $request, $form_id) {
        $data = json_decode($request->get('data'),true);
        $use_unique_identifyer = $request->get('use_unique_identifyer');
        $unique_identifyer = $request->get('unique_identifyer');
        $user = Auth::user();
        $form = $this->_getForm($form_id, $user,1,$decorate=false);
        $form_data = $form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user);
        $reference_elements = $form_data[0];
        $submissions = $form_data[1];
        $combined_reference_elements = [];
        foreach($reference_elements as $form_id=>$form_elements) {
            $combined_reference_elements += $form_elements;
        }
        foreach($data as $row) {
            $submission_input_data = [];
            foreach($reference_elements[$form->id] as $el_id=>$el) {
                if(!$el->input) {
                    continue;
                }
                if(!array_key_exists($el_id,$row)) {
                    $submission_input_data[$el_id] = $this->findReferencedSubmissionFromValues($row,$el,$form,$combined_reference_elements,$submissions);
                    continue;
                }
                $submission_input_data[$el_id] = $this->parseInputData($row[$el_id],$el,$form,$combined_reference_elements);
            }
            $new_submission = new Submission();
            $new_submission->form()->associate($form);
            $new_submission->_data = $submission_input_data;
            if((!array_key_exists('owner_type',$row) || $row['owner_type']===null) && ($row['owner_id']===0 || $row['owner_id'])) {
                $owner = Group::where('name',$row['owner_id'])->first();
                if(!$owner) {
                    $owner = User::getUserByName($row['owner_id']);
                }
            } else if($row['owner_id']!==0 && !$row['owner_id']) {
                $owner = Group::findOrFail(29);
            } else if($row['owner_type']==0) {
                $owner = User::findOrFail($row['owner_id']);
                if(!$owner) {
                    $owner = User::getUserByName($row['owner_id']);
                }
            } else if($row['owner_type']==1) {
                $owner = Group::find($row['owner_id']);
                if(!$owner) {
                    $owner = Group::where('name',$row['owner_id'])->first();
                }
            } else if($row['owner_type']===null) {
                return "test";
            }
            if(!$owner) {
                $owner = Group::findOrFail(29);
            }
            $new_submission->owner()->associate($owner);

            $new_submission->save();
        }
    }


    public function destroy($id)
    {
        $form = Form::findOrFail($id);
        foreach($form->form_elements as $form_element) {
            $form_element->delete();
        }
        $form->user_observers()->detach();
        $form->group_observers()->detach();
        DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->delete();
        DB::table('agent_user_submissions_permissions')->where("form_id", $form->id)->delete();
               
        
        $form->delete();

        
        return $this->index();
    }

}
