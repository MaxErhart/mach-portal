<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all();
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
        return response()->json($user);
    }

}
