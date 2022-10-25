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
        "date" => [
            "regex" => '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/',
            "error_message" => "Wrong date input"            
        ],              
    ];


    private function getPermissionRank($permission) {
        if($permission=="delete") {
            return 3;
        } else if($permission=="edit") {
            return 2;
        } else if($permission=="view") {
            return 1;
        }
        return 0;
    }

    private $userRightsOnSubmissionsOfGroups = null;
    private function getUserRightsOnSubmissionsOfGroups($user) {
        // if previous calculated value exists get that
        if($this->userRightsOnSubmissionsOfGroups!=null) {
            return $this->userRightsOnSubmissionsOfGroups;
        }

        // get explicite rights for all of the user's groups
        $groupRights = [];
        foreach($user->groups as $group) {
            $groupRights = array_merge($groupRights, $group->submission_permissions_groups->toArray());
        }

        // merge all rights 
        $rights = [];
        foreach($groupRights as $groupRight) {
            if(!in_array($groupRight["id"], $rights)) {
                $rights[$groupRight["id"]] = $this->getPermissionRank($groupRight["pivot"]["permission"]);
            } else {
                if($rights[$groupRight["id"]]<$this->getPermissionRank($groupRight["pivot"]["permission"])) {
                    $rights[$groupRight["id"]] = $this->getPermissionRank($groupRight["pivot"]["permission"]);
                }
            }
        }

        $this->userRightsOnSubmissionsOfGroups = $rights;
        return $rights;
    }
    
    private function getSubmissionRight($user, $sub) {

        // view:1 < edit:2 < delete:3
        if($sub->form->creator_id==$user->id) {
            return 3;
        } else if($sub->user->id==$user->id) {
            return 3;
        } else if($user->groups->pluck('id')->contains(25)) {
            return 3;
        }

        $rights = $this->getUserRightsOnSubmissionsOfGroups($user);
        // merge all rights to the groups of the submission owner
        $highest = 0;
        foreach($sub->user->groups as $group) {
            if(array_key_exists($group->id, $rights) && $rights[$group->id]>$highest) {
                $highest = $rights[$group->id];
            }
        }
        return $highest;
    }


    public function index(Request $request)
    {

        $request->merge(["form_id"=>$request->form_id]);
        $request->validate([
            "form_id"=>"required|integer",
        ]);
        $user = Auth::user();
        $form = Form::where('id', $request->form_id)->get()[0];

        $submissions = Submission::where('form_id', $request->form_id)->get();
        $tmp = [];
        foreach($submissions as $key=>$submission) {
            $subRight = $this->getSubmissionRight($user, $submission);
            if($subRight>0) {
                $submission->permission = $subRight;
                $tmp[] = $submission;
                // $submissions->forget($key);
                // continue;
            }
            // $submission->permission = $subRight;
        }
        $submissions = collect($tmp);

        foreach($submissions as $submission) {
            $submission->user;
            $submission->form;
            $submission->form_elements = $submission->form_elements()->orderBy('position', 'asc')->get();
        }

        $submissions->map(function($submission) {
            return $submission->form_elements->map(function($element) {
                $element->pivot->data = json_decode($element->pivot->data);
                return;
            });
        });
        
        
        $form = Form::where('id', $request->form_id)->get()[0];
        $file = $this->export($submissions, $form, $user);
        $submissions->map(function($submission) use ($file) {
            $submission["export"] = $file;
            return $submission;
        });      
        
        return response()->json($submissions);
    }

    public function custom_validate($request, $form_elements) {
        foreach($form_elements as $form_element) {
            if($form_element->component=="InputElement") {
                $v = $request->get("{$form_element->id}");
                if($form_element->data["required"]) {
                    if($v==null) {
                        abort(response()->json([
                            "message"=>"Input: \"{$form_element->data['label']}\" is required",
                            "element_id"=>$form_element->id,
                        ], 500));
                    }
                }
                if(!preg_match($this->settings[$form_element->data["type"]]["regex"], $v)) {
                    abort(response()->json([
                        "message"=>$this->settings[$form_element->data["type"]]["error_message"],
                        "element_id"=>$form_element->id,
                    ], 500));
                }
            } else if($form_element->component=="FileUploadElement") {
                if($form_element->data["required"]) {
                    $v = $request->file($form_element->id);
                    if($v==null) {
                        abort(response()->json([
                            "message"=>"Input: \"{$form_element->data['label']}\" is required",
                            "element_id"=>$form_element->id,
                        ], 500));
                    }                    
                }
            } else if($form_element->component=="FileUploadElement") {
                if($form_element->data["required"]) {
                    $v = $request->get("{$form_element->id}");
                    if($v==null) {
                        abort(response()->json([
                            "message"=>"Input: \"{$form_element->data['label']}\" is required",
                            "element_id"=>$form_element->id,
                        ], 500));
                    }                    
                }
            } else if($form_element->component=="Checkbox") {
                if($form_element->data["required"]) {
                    $v = filter_var($request->get($form_element->id), FILTER_VALIDATE_BOOLEAN);
                    if(!$v) {
                        // abort(500, "Checkbox {$form_element->data['label']} is required");
                        abort(response()->json([
                            "message"=>"Checkbox: \"{$form_element->data['label']}\" is required",
                            "element_id"=>$form_element->id,
                        ], 500));
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
        // return response()->json(Auth::user());
        if($form->multiple_submissions==0 && count($form->submissions()->where('user_id', Auth::user()->id)->get()->toArray())>0) {
            abort(500, "Multiple submissions not allowed.");
        }
        $form_elements = $form->form_elements;
        $this->custom_validate($request, $form_elements);

        $data = [];
        foreach($form_elements as $form_element) {
            if($form_element->component=="InputElement" || $form_element->component=="SelectElement" || $form_element->component=="SelectReferenceElement") {
                $data[$form_element->id] = ["data"=>json_encode($request->get($form_element->id))];
            }
            if($form_element->component=="FileUploadElement") {
                $path = $request->file($form_element->id)->store("file_submissions_form_{$form->name}");
                $data[$form_element->id] = ["data"=>json_encode($path)];
            }
            if($form_element->component=="Checkbox") {
                $data[$form_element->id] = ["data"=>json_encode(filter_var($request->get($form_element->id), FILTER_VALIDATE_BOOLEAN))];
            }       
        }
    

        $newSubmission = new Submission();

        $newSubmission->user()->associate(Auth::user());
        $newSubmission->form()->associate($form);
        $newSubmission->save();
        $newSubmission->form_elements()->sync($data);
        $newSubmission->form_elements->map(function($element) {
            return $element->pivot->data = json_decode($element->pivot->data);
        });
        return response()->json($newSubmission);
    }

    public function update(Request $request, $subId)
    {       
        $submission = Submission::findOrFail($subId);
        $user = Auth::user();

        $subRight = $this->getSubmissionRight($user, $submission);
        if($subRight<2) {
            return response('Unauthorized.', 401);
        }

        $form_elements = $submission->form_elements;
        $this->custom_validate($request, $form_elements);

        $data = [];
        foreach($form_elements as $form_element) {
            if($form_element->component=="InputElement" || $form_element->component=="SelectElement") {
                $data[$form_element->id] = ["data"=>json_encode($request->get($form_element->id))];
            }
            if($form_element->component=="FileUploadElement") {
                $path = $request->file($form_element->id)->store("file_submissions_form_{$form->name}");
                $data[$form_element->id] = ["data"=>json_encode($path)];
            }
            if($form_element->component=="Checkbox") {
                $data[$form_element->id] = ["data"=>json_encode(filter_var($request->get($form_element->id), FILTER_VALIDATE_BOOLEAN))];
            }
        }
        // return response()->json($data);

        foreach($data as $id=>$val) {
            $submission->form_elements()->updateExistingPivot($id, $val);

        }
        $submission->save();


        $submission = Submission::findOrFail($subId);
        $submission->user;
        $submission->form;
        $submission->form_elements->map(function($element) {
            $element->pivot->data = json_decode($element->pivot->data);
            return;
        });
        return response()->json($submission);
    }


    public function destroy($id)
    {
        $submission = Submission::findOrFail($id);
        $form_id = $submission->form->id;
        $user = Auth::user();
        $subRight = $this->getSubmissionRight($user, $submission);
        if($subRight<2) {
            return response('Unauthorized.', 401);
        }
        $submission->form_elements()->detach();
        $submission->delete();


        $submissions = Submission::where('form_id', $form_id)->get();
        foreach($submissions as $key=>$submission) {
            $subRight = $this->getSubmissionRight($user, $submission);
            if($subRight==0) {
                $submissions->forget($key);
                continue;
            }
            $submission->permission = $subRight;
        }

        foreach($submissions as $submission) {
            $submission->user;
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
    
    private function export($data, $form, $user)
    {
        $submit_components = [
            "InputElement",
            "Checkbox",
            "SelectElement",
            "SelectReferenceElement",
        ];
        $export = [];
        foreach($data as $data_row) {
            $export_line = [
                "id"=>$data_row["id"],
            ];
            if($data_row->has("user")) {
                foreach($data_row->user->toArray() as $key=>$value) {
                    if(is_array($value)) {
                        $export_line["user.$key"] = json_encode($value);
                    } else {
                        $export_line["user.$key"] = $value;
                    }
                }
            }
            foreach($data_row["form_elements"] as $form_element) {
                $export_line["input.{$form_element['data']['label']}"] = $form_element["pivot"]["data"];
            }
            array_push($export, $export_line);
        }
        // $export_string = $this->tabDelimited($export);
        $export_string = $this->commaDelimited($export);
        $filepath = "D:\inetpub\MPortal\dfiles\submissions\\";
        $uniqId = substr(md5(uniqid(rand(), true)), 0, 6);
        $date = date('Y_m_d');
        // $filename = $date."_".$form["id"]."_".$user->id."_".$uniqId.".csv";
        $filename = $date."_".$form["id"]."_".$user->id.".csv";
        $file = $filepath.$filename;
        file_put_contents($file, $export_string);
        return $filename;
    }

    private function commaDelimited($data) {
        $string = "";
        $flag = false;
        foreach($data as $row) {
          if(!$flag) {
            $string .= implode(",", array_keys($row)) . "\r\n";
            $flag = true;
          }
          array_walk($row, __NAMESPACE__ . '\cleanData');
          $string .= implode(",", array_values($row)) . "\r\n";
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