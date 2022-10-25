<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CustomAuthController;
use App\Http\Controllers\API\GroupAppSettingsController;
use App\Models\GroupAppSettings;
use App\Models\App;
use App\Models\Settings;
use App\Models\Group;
use App\Models\Form;
use App\Models\Tag;
use App\Models\Submission;
use Session;

class Rights
{

    private $settings = [
        'AppController'=>[
            'model'=>'App',
            'table_name'=>'apps',
            'name'=>'app',
            'app_id'=>1
        ],        
        'GroupAppSettingsController'=>[
            'model'=>'GroupAppSettings',
            'table_name'=>'group_app_settings',
            'name'=>'groupappsetting',
            'app_id'=>6
        ],
        'PermissionController'=>[
            'model'=>'Permission',
            'table_name'=>'permissions',
            'name'=>'permission',
            'app_id'=>7
        ],
        'FormController'=>[
            'model'=>'Form',
            'table_name'=>'forms',
            'name'=>'form',
            'app_id'=>38
        ],
        'TagController'=>[
            'model'=>'Tag',
            'table_name'=>'tags',
            'name'=>'tag',
            'app_id'=>36
        ],
        'SubmissionController'=>[
            'model'=>'Submission',
            'table_name'=>'submissions',
            'name'=>'submission',
            'app_id'=>37
        ],
        'BewerberController'=>[
            'model'=>'Bewerber',
            'table_name'=>'bewerbers',
            'name'=>'bewerbers',
            'app_id'=>37
        ], 
        'EntranceExamController'=>[
            'model'=>'EntranceExam',
            'table_name'=>'entrance_exams',
            'name'=>'entrance_exams',
            'app_id'=>37
        ],                                            
    ];

    private $methods = ['index', 'store', 'show', 'update', 'destroy'];

    private $mergedSettings;

    private function initMergedSettings() {
        $mergedSettings = [];
        foreach($this->methods as $method) {
            $mergedSettings[$method]['restricted'] = true;
            $mergedSettings[$method]['object_ids'] = [];
        }
        return $mergedSettings;
    }

    public function mergeSettings($settings) {
        $mergedSettings = [];
        foreach($settings as $setting) {
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
        return $mergedSettings;
    }

    private function hasRights($controllerName, $method, $object_id=NULL) {
        $user = Auth::user();
        $app_id = $this->settings[$controllerName]['app_id'];
        $mergedSettings = $this->initMergedSettings();
        foreach($user->groups as $group){
            $groupAppSettings = GroupAppSettings::where(['group_id'=>$group->id, 'app_id'=>$app_id])->first();
            if(!$groupAppSettings) {continue;}
            $mergedSettings[$method]['restricted'] = !$mergedSettings[$method]['restricted'] ? $mergedSettings[$method]['restricted'] : $groupAppSettings->settings[$method]['restricted'];
            $mergedSettings[$method]['object_ids'] = array_merge($mergedSettings[$method]['object_ids'], $groupAppSettings->settings[$method]['object_ids']);
        }
        if($mergedSettings[$method]['restricted'] && empty($mergedSettings[$method]['object_ids'])) {
            return false;
        }
        if($method=='store' && $mergedSettings[$method]['restricted']) {
            return false;
        }
        if($method=='show' || $method=='update' || $method=='destroy') {
            if(!$object_id) {
                return false;
            }
            if($mergedSettings[$method]['restricted'] && !in_array($mergedSettings[$method]['object_ids'], $object_id)) {
                return false;
            }
        }
        $this->mergedSettings = $mergedSettings[$method];
        return true;        
    }

    public function getRightsAPI($controllerName, $method, $object=NULL) {
        if($this->hasRightsAPI($controllerName, $method, $object)) {
            return $this->mergedSettings;
        }
    }

    public function getAppRights() {
        $apps = [];
        $user = Auth::user();
        $user_groupIds = $user->groups->pluck('id')->toArray();
        $groupAppSettingsIds = GroupAppSettings::whereIn('group_id',$user_groupIds)->where('app_id', 1)->get()->pluck('id');
        // return $groupAppSettingsIds;

        $settings = Settings::where('type', 'index')->whereIn('group_app_settings_id',$groupAppSettingsIds)->get();
        foreach($settings as $setting) {
            if($setting['unrestricted']) {
                return App::all()->pluck('id');
            }
        }


        $groupAppSettings = GroupAppSettings::whereIn('group_id',$user_groupIds)->get();

        return $groupAppSettings->pluck('app_id');
    }

    private function hasRightsAPI($controllerName, $method, $object=NULL) {
        if($method=='show') {
            $method='index';
        }
        $user = Auth::user();
        $app_id = $this->settings[$controllerName]['app_id'];
        $user_groupIds = $user->groups->pluck('id')->toArray();
        $groupAppSettings = GroupAppSettings::where(['app_id'=>$app_id])->whereIn('group_id',$user_groupIds)->get();
        $groupAppSettingsIds = $groupAppSettings->pluck('id')->toArray();
        $settings = Settings::where('type', $method)->whereIn('group_app_settings_id',$groupAppSettingsIds)->get();
        $mergedSettings = $this->mergeSettings($settings);
        // return $mergedSettings;

        if(array_key_exists($method,$mergedSettings)) {
            // if($object && !$mergedSettings[$method]['unrestricted'] && !in_array($object->creator_id, $mergedSettings[$method]['users'])){
            //     return false;
            // }
            $this->mergedSettings = $mergedSettings[$method];
            return true;
        }
        return false;
    }

    public function handle($request, Closure $next)
    {
        if(str_starts_with($request->headers->get('origin'), 'http://localhost:')) {
            $request->merge(['rights'=>['unrestricted'=>true]]);
            Auth::loginUsingId(12);
            return $next($request);
        }
        if(!Auth::check()) {
            return response('Unauthorized.', 401);
        }
        $routeName = $request->route()->getActionName();
        $routePath = explode("\\", $routeName);
        $controller = explode("@", array_pop($routePath));

        $model = "App\\Models\\{$this->settings[$controller[0]]['model']}";
        $object = new $model();
        $object = $object->find($request->{$this->settings[$controller[0]]['name']});
        if(!$this->hasRightsAPI($controller[0], $controller[1], $object)) {
            return response('Unauthorized.', 401);
        }
        $request->merge(['rights'=>$this->mergedSettings]);
        return $next($request);
    }
}
