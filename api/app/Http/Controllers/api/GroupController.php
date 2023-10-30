<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $groups = Group::all();
        foreach($groups as $key=>$group) {
            $permission = $group->getUserPermissionOnGroup($user);
            if($permission<=0) {
                $groups->forget($key);
            } else {
                $group->users;
                $group->app_permissions;
            }
        }
        $groups = array_values($groups->toArray());
        return response()->json($groups);
    }

    public function show($id)
    {
        $group = Group::findOrFail($id);

        return response()->json($group);
    }
}
