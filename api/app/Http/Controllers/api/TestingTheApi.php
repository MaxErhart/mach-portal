<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\User;
use App\Models\Group;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;

use App\Models\GroupAppSettings;
use App\Models\Settings;
use App\Models\App;
use App\Models\Form;
use App\Models\FormElement;
use App\Models\Submission;
use App\Models\Stundenzettel;
use App\Models\StundenzettelArbeitstag;
use App\Models\Bescheid;
use \Datetime;
use App\Models\Bewerber;
use App\Models\Action;
use DB;
use Illuminate\Support\Facades\URL;


require_once(base_path().'\app\pdf_templates\admission.php');
require_once(base_path().'\app\pdf_templates\rejection.php');
require_once(base_path().'\app\pdf_templates\bestanden.php');
require_once(base_path().'\app\pdf_templates\nicht_bestanden.php');
require_once(base_path().'\app\pdf_templates\nicht_bestanden_nicht_teilgenommen.php');
require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_1.php');
require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_2.php');
require_once(base_path().'\app\pdf_templates\Lehrverpflichtung\bescheid_3.php');
use \PdfTemplates\Admission;

class TestingTheApi extends Controller
{

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

    public function test_post(Request $request) {
        return "good post test";

        // $groups = Group::all();
        // $users = User::all();
        // $wildcard_source = $request->get("wildcard_source");

        // $wildcards_sources = [];
        // foreach($groups as $group) {
        //     if($wildcards = $this->getWildcards("MACH-{#1}-Lehre", $group->name)) {
        //         $wildcards_sources[$group->id] = $wildcards;
        //     }
        // }

        // $wildcards_targets = [];
        // foreach($groups as $group) {
        //     if($wildcards = $this->getWildcards("MACH-{#2}-Lehre", $group->name)) {
        //         $wildcards_targets[$group->id] = $wildcards;
        //     }
        // }

        // $pairs = [];

        // foreach($wildcards_sources as $source_group_id=>$wildcards_source) {
        //     foreach($wildcards_targets as $target_group_id=>$wildcards_target) {
        //         $is_good = true;
        //         foreach($wildcards_source as $wildcard_source=>$source_value) {
        //             if($is_good && array_key_exists($wildcard_source,$wildcards_target) && $source_value!==$wildcards_target[$wildcard_source]) {
        //                 $is_good = false;
        //             }
        //         }
        //         if($is_good) {
        //             $pairs[] = [$source_group_id,$target_group_id];
        //         }
    
        //     }
        // }


        // return [$wildcards_sources,$wildcards_targets,$pairs];



        // return response()->json($group_wildcards);





    }

    public function test(Request $request) {
        $date_cutoff = now()->subHours(8);

        $actions = Action::orderBy('created_at', 'desc')->where('request_url','https://www-3.mach.kit.edu/api/public/index.php/api/auth/login')->whereDate('created_at','>',$date_cutoff->format('Y-m_d'))->orWhere(function ($query) use ($date_cutoff) {
            $query->whereDate('created_at','=',$date_cutoff->format('Y-m_d'))->whereTime('created_at', '>',$date_cutoff->toTimeString());
        })->get();

        $recent_user_ids = $actions->pluck('user_id')->unique();
        $recent_user_actions = [];
        $date_cutoff = now()->subHours(1);
        foreach($recent_user_ids as $key=>$user_id) {
            $last_user_action = Action::orderBy('created_at', 'desc')->where('user_id',$user_id)->first();
            if($last_user_action->getRawOriginal('created_at')>$date_cutoff && ($last_user_action->request_controller!=="App\Http\Controllers\API\CustomAuthController" || $last_user_action->request_controller_method!=="logout")) {
                $recent_user_actions[] = $last_user_action;
                continue;
            }
            $recent_user_ids->forget($key);
        }
        return $recent_user_actions;

    }

}
