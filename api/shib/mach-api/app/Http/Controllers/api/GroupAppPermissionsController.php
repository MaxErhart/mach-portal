<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\App;
use App\Models\Group;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;


class GroupAppPermissionsController extends Controller
{

    public function show(Request $request, $id) {
        $group = Group::findOrFail($id);
        $group->app_permissions;
        return response()->json($group);
    }

    public function store(Request $request) {
        $group = Group::where('id', $request->get('group'))->first();
        $apps = json_decode($request->get('apps'));
        $group->app_permissions()->sync($apps);
        return response()->json($apps);
    }
}
