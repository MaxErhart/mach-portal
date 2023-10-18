<?php

namespace App\Http\Controllers\api;
use App\Http\Middleware\Rights;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\User;
use App\Models\Group;
use App\Models\Permission;
use App\Models\Submission;
use App\Models\GroupAppSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Str;
class CustomAuthController extends Controller
{

    private const ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES = [
        ["name"=>"lastname","type"=>"string"],
        ["name"=>"firstname","type"=>"string"],
        ["name"=>"email","type"=>"string"],
        ["name"=>"matriculationNumber","type"=>"integer"],
        ["name"=>"fieldOfStudy","type"=>"integer"],
        ["name"=>"fieldOfStudyId","type"=>"integer"],
        ["name"=>"fieldOfStudyText","type"=>"string"],
        ["name"=>"degree","type"=>"integer"],
        ["name"=>"degreeText","type"=>"string"],
    ];

    private function getShibAttributes() {
        $shibAttributeNames = self::ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES;
        $shibAttributes = [];
        if(array_key_exists("shib_id", $_SERVER) && $_SERVER["shib_id"]){
            $shibAttributes["affiliation"] = explode(";", $_SERVER["affiliation"]);
            if(array_key_exists("memberOf", $_SERVER)) {
                $shibAttributes["memberOf"] = explode(";", $_SERVER["memberOf"]);
                $shibAttributes["memberOf"] = array_filter($shibAttributes["memberOf"], function($groupName) {
                    return str_starts_with($groupName, 'MACH-Portal-');
                });
            } else {
                $shibAttributes["memberOf"] = [];
            }

            foreach($shibAttributeNames as $attribute) {
                if(array_key_exists($attribute["name"], $_SERVER)) {
                    $shibAttributes[$attribute["name"]] = $_SERVER[$attribute["name"]];
                }
            }
        }
        array_push($shibAttributes["memberOf"], 'MACH-Portal-default');
        if(in_array('student@kit.edu', $shibAttributes["affiliation"]) && $shibAttributes['matriculationNumber']) {
            $shibAttributes["memberOf"][] = 'MACH-Portal-Student';
            if($shibAttributes["fieldOfStudyId"]==604) {
                $shibAttributes["memberOf"][] = 'MACH-Portal-Student-MACH';
            } else if($shibAttributes["fieldOfStudyId"]==704) {
                $shibAttributes["memberOf"][] = 'MACH-Portal-Student-MATWERK';
            } else if($shibAttributes["fieldOfStudyId"]==480) {
                $shibAttributes["memberOf"][] = 'MACH-Portal-Student-MIT';
            } else if($shibAttributes["fieldOfStudyId"]==804) {
                $shibAttributes["memberOf"][] = 'MACH-Portal-Student-MEI';
            }
        }
        return $shibAttributes;        
    }

    private function shibAttributesChanged($dbAttributes, $shibAttributes) {
        $shibAttributeNames = self::ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES;
        foreach($shibAttributeNames as $attribute) {
            if(!array_key_exists($attribute["name"], $shibAttributes)) {
                continue;
            } else if(!array_key_exists($attribute["name"], $dbAttributes)) {
                throw new Exception('Attribute not in users table.');
            }            
            if($dbAttributes[$attribute["name"]]!==$shibAttributes[$attribute["name"]]) {
                return true;
            }
        }
        return false;
    }

    private function updateValues($user, $values) {
        foreach(self::ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES as $attribute) {
            if(!array_key_exists($attribute["name"], $values)) {
                continue;
            } else if(!array_key_exists($attribute["name"], $user->getAttributes())) {
                throw new Exception('Attribute not in users table.');
            }
            $attributeName = $attribute["name"];
            $user->$attributeName = $values[$attribute["name"]];
        }
    }

    public function mergeSettings($appSettings) {
        $mergedSettings = [];
        foreach($appSettings as $settings) {
            foreach($settings['settings'] as $setting) {
                if($setting->unrestricted) {
                    $mergedSettings[$setting->type] = ['unrestricted'=>true];
                    continue;
                } else if(array_key_exists($setting->type, $mergedSettings) && $mergedSettings[$setting->type]['unrestricted']){
                    continue;
                }
                $users = $setting->users->pluck('id')->toArray();
                $groups = Group::find($setting->groups->pluck('id')->toArray());
                foreach($groups as $group) {
                    $users = array_unique(array_merge($users, $group->users->pluck('id')->toArray()), SORT_REGULAR);
                }
                if(!array_key_exists($setting->type, $mergedSettings)) {
                    $mergedSettings[$setting->type] = ['users'=>$users,'unrestricted'=>false];
                } else {
                    $mergedSettings[$setting->type]['users'] = array_unique(array_merge($mergedSettings[$setting->type]['users'],$users), SORT_REGULAR);
                }
            }
        }

        return $mergedSettings;
    }

    private function getSettings() {
        $user = Auth::user();
        $groupIds = $user->groups->pluck('id')->toArray();
        // foreach($user->groups as $group) {
        //     foreach($group->group_app_settings as $groupAppSetting) {
        //         $groupAppSetting->apps;
        //     }
        // }
        // $user->groups->group_app_settings->apps;
        $groupAppSettings = GroupAppSettings::whereIn('group_id',$groupIds)->get();
        $settings_apps = NULL;
        foreach($groupAppSettings as $groupAppSetting) {
            $groupAppSetting->settings;
            $groupAppSetting->apps;
        }
        $mergedSettings=[];
        foreach($groupAppSettings->groupBy('app_id') as $app=>$appSettings) {
            $mergedSettings[$app] = $this->mergeSettings($appSettings);
        }
        return $mergedSettings;
    }

    private function updateShibAttributes($user, $values) {
        foreach(self::ADDITONAL_SHIBBOLETH_ATTRIBUTE_NAMES as $attribute) {
            if(!array_key_exists($attribute["name"], $values)) {
                continue;
            } else if(!array_key_exists($attribute["name"], $user->getAttributes())) {
                throw new Exception('Attribute not in users table.');
            }
            $attributeName = $attribute["name"];
            if($attribute["type"]=="integer") {
                $user->$attributeName = intval($values[$attribute["name"]]);
            } else {
                $user->$attributeName = $values[$attribute["name"]];
            }
        }
        $user->affiliation = $values["affiliation"];

        $groups = [];
        foreach($values['memberOf'] as $group) {
            $groupName = ['name' => $group];
            $group = Group::firstOrCreate($groupName);
            $groups[] = $group;
        }
        $groups = collect($groups);
        $user->groups()->sync($groups->pluck('id'));
    }

    public function shibLogin(Request $request) {
        // developement login
        // $user = Auth::loginUsingId(4);
        $whitelist = [
            '2a00:1398:4:a000::1:2',
        ];
        if(in_array($_SERVER['REMOTE_ADDR'], $whitelist) && str_starts_with($request->headers->get('origin'), 'http://localhost:')) {
            $user = Auth::loginUsingId(4);
            // $user = Auth::loginUsingId(6);
            // $user = Auth::loginUsingId(9);
            // $user = Auth::loginUsingId(425);
            // $user = Auth::loginUsingId(12);
            $user->rightsOnApps();
            return response()->json($user);
        }

        // get server variables containing information send by shibboleth
        $_SERVER = array_map('utf8_encode', $_SERVER);

        // check if shibboleth data is present 
        if(!array_key_exists('shib_id', $_SERVER) || $_SERVER["shib_id"]===NULL) {
            $this->logout($request);
            abort(response()->json([
                "message"=>"Not logged in",
            ], 403));
        }

        // return currently authenticated user
        if(Auth::check() && $request->session()->has('Shib-Session-Index') && $request->session()->get('Shib-Session-Index')==$_SERVER['Shib-Session-Index']) {
            $user = Auth::user();
            $user->rightsOnApps();
            return response()->json($user);
        }


        // update user information and authenticate user based on shibboleth data 
        $shibId = ['shib_id' => explode('@', $_SERVER['shib_id'])[0]];
        $shibAttributes = $this->getShibAttributes();
        $user = User::firstOrCreate($shibId, $shibAttributes);
        $this->updateShibAttributes($user, $shibAttributes);
        $user->save();
        Auth::login($user, false);
        $user->rightsOnApps();
        $request->session()->put('Shib-Session-Index', $_SERVER['Shib-Session-Index']);
        return response()->json($user);
    }

    public function logout(Request $request) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // return response()->json(Auth::user());
        return response()->json('logout');
    }

}
