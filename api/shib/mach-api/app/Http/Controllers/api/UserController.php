<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user_loggedin = Auth::user();

        $users = User::all();
        foreach($users as $key=>$user) {
            $permission = $user->getUserPermissionOnUser($user_loggedin);
            if($permission<=0) {
                $users->forget($key);
            }
        }
        $users = array_values($users->toArray());
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {       
        if(!filter_var($request->get("consent"), FILTER_VALIDATE_BOOLEAN)) {
            abort(response()->json([
                "message"=>"Input: consent is required",
                "element_id"=>'consent',
            ], 500));
        }
        $user = User::findOrFail($id);
        $user->address_street = $request->get("address_street");
        $user->address_city = $request->get("address_city");
        $user->address_postalcode = $request->get("address_postalcode");
        $user->address_country = $request->get("address_country");
        $user->private_email = $request->get("private_email");
        $user->save();
        $user->rightsOnApps();
        return response()->json($user);
    }

}
