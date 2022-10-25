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
use \Datetime;

class TestingTheApi extends Controller
{

    public function test(Request $request) {
        $user = Auth::loginUsingId(4);
        // $user->rightsOnApps();
        $_SERVER = array_map('utf8_encode', $_SERVER);
        return response()->json($_SERVER);
        // foreach($user->groups as $group) {
        //     $group->group_app_settings;
        // }

        // $form_ids_ext = $user->view_forms()->get();
        // foreach($user->groups as $group) {
        //     $form_ids_ext = $form_ids_ext->concat($group->view_forms()->get());
        $routeName = $request->route()->getActionName();
        // }
        return response()->json($routeName);

    }

}
