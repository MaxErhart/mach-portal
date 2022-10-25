<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GroupAppSettings;
use App\Models\App;
use App\Models\Group;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;


class GroupAppSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $rights = $request->rights;

        if($rights['unrestricted']) {
            $groupAppSettings = GroupAppSettings::all();
        } else {
            $groupAppSettings = GroupAppSettings::whereIn('creator_id', $rights['users'])->get();
        } 
        foreach($groupAppSettings as $groupAppSetting){
            $groupAppSetting->apps;
            $groupAppSetting->groups;
            foreach($groupAppSetting->Settings as $setting) {
                $setting->users;
                $setting->groups;
            }
        }
        return response()->json($groupAppSettings);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "groupId"=>"required",
            "appId"=>"required",
            "settings"=>"required",
        ]);
        $settings = json_decode($request->get("settings"), true);
        $groupAppSettings = new GroupAppSettings;
        $groupAppSettings->creator_id=Auth::user()->id;
        $groupAppSettings->groups()->associate($request->get("groupId"));
        $groupAppSettings->apps()->associate($request->get("appId"));
        $groupAppSettings->save();
        foreach($settings as $type=>$agents) {
            if(array_key_exists("unrestricted", $agents)) {
                $newSettings = new Settings(["type"=>$type, "unrestricted"=>$agents["unrestricted"]]);
                $groupAppSettings->settings()->save($newSettings);

            } else {
                $newSettings = new Settings(["type"=>$type]);
                $groupAppSettings->settings()->save($newSettings);
                if(count($agents["groups"])>0){
                    $groupIds = array_column($agents["groups"], 'id');
                    $newSettings->groups()->sync($groupIds);
                }
                if(count($agents["users"])>0){
                    $userIds = array_column($agents["users"], 'id');
                    $newSettings->users()->sync($userIds);
                }
            }

            
        }
        $groupAppSettings->save();
        $groupAppSettings->apps;
        $groupAppSettings->groups;
        foreach($groupAppSettings->Settings as $setting) {
            $setting->users;
            $setting->groups;
        }
        return response()->json($groupAppSettings);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $groupAppSettings = GroupAppSettings::findOrFail($id);
        $groupAppSettings->apps;
        $groupAppSettings->groups;
        foreach($groupAppSettings->Settings as $setting) {
            $setting->users;
            $setting->groups;
        }
        return response()->json($groupAppSettings);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            "groupId"=>"required",
            "appId"=>"required",
            "settings"=>"required",
          ]);
        $settings = json_decode($request->get("settings"), true);
        $groupAppSettings = GroupAppSettings::findOrFail($id);
        // $groupAppSettings->creator_id=Auth::user()->id;
        $groupAppSettings->groups()->associate($request->get("groupId"));
        $groupAppSettings->apps()->associate($request->get("appId"));
        $groupAppSettings->save();
        $oldSettings = $groupAppSettings->settings;
        foreach($oldSettings as $oldSetting) {
            $oldSetting->users()->detach();
            $oldSetting->groups()->detach();
            $oldSetting->delete();
        }
        foreach($settings as $type=>$agents) {
            if(array_key_exists("unrestricted", $agents)) {
                $newSettings = new Settings(["type"=>$type, "unrestricted"=>$agents["unrestricted"]]);
                $groupAppSettings->settings()->save($newSettings);

            } else {
                $newSettings = new Settings(["type"=>$type]);
                $groupAppSettings->settings()->save($newSettings);
                if(count($agents["groups"])>0){
                    $groupIds = array_column($agents["groups"], 'id');
                    $newSettings->groups()->sync($groupIds);
                }
                if(count($agents["users"])>0){
                    $userIds = array_column($agents["users"], 'id');
                    $newSettings->users()->sync($userIds);
                }
            }
        }
        $groupAppSettings->save();
        $groupAppSettings->apps;
        $groupAppSettings->groups;
        foreach($groupAppSettings->Settings as $setting) {
            $setting->users;
            $setting->groups;
        }
        return response()->json($groupAppSettings);
        
    }    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {      
        $groupAppSettings = GroupAppSettings::findOrFail($id);
        foreach($groupAppSettings->Settings as $settings) {
            $settings->users()->detach();
            $settings->groups()->detach();
            $settings->delete();
        }        
        $groupAppSettings->delete();

        return response()->json($groupAppSettings::all());
    }
}
