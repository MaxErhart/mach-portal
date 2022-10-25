<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $groups = Group::all();
        foreach($groups as $group) {
            $group->app_permissions;
        }
        return response()->json($groups);
    }

    public function show($id)
    {
        $group = Group::findOrFail($id);

        return response()->json($group);
    }
}
