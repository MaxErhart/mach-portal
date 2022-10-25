<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormElement;
use App\Models\Tag;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use DB;
class FormControllerNoLogin extends Controller
{   


    public function index(Request $request)
    {

        return response('Not found.', 404);
    }

    public function store(Request $request)
    {
        return response('Not found.', 404);
    }


    public function update(Request $request)
    {
        return response('Not found.', 404);
    }

    public function destroy(Request $request)
    {
        return response('Not found.', 404);
    }

    public function show($id)
    {

        $form = Form::findOrFail($id);
        if($form->no_login!=1) {
            return response('Unauthorized.', 401);
        }
        $form->form_elements = $form->form_elements()->orderBy('position', 'asc')->get();
        $form->tags;
        $form->user_observers;
        $form->group_observers;

        $permissions = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
        $form->setAttribute("permissions", $permissions); 
        return response()->json($form);
    }
}
