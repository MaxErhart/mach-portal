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
class FormController extends Controller
{

    private function getPermissionRank($permission) {
        if($permission=="delete") {
            return 3;
        } else if($permission=="edit") {
            return 2;
        } else if($permission=="view" || $permission=="submit") {
            return 1;
        }
        return 0;
    }
    private $userFormRightsCalculated = false;
    private $userFormRights = null;
    private function getUserFormRights($user) {
        if($this->userFormRightsCalculated) {
            return $this->userFormRights;
        }
        $forms = $user->view_forms()->get();
        foreach($user->groups as $group) {
            $forms = $forms->concat($group->view_forms()->get());
        }
        $formsRightsDict = [];
        foreach($forms as $form) {
            $formsRightsDict[$form->id] = $this->getPermissionRank($form->pivot->permission);
        }
        $this->userFormRights = $formsRightsDict;
        $this->userFormRightsCalculated = true;
        return $formsRightsDict;
    }

    private function getFormRight($user, $form) {
        if($form->creator_id==$user->id) {
            return 3;
        }
        if($user->groups->pluck('id')->contains(25)) {
            return 3;
        }
        if($form->display==0) {
            return 0;
        }

        $userFormRights = $this->getUserFormRights($user);
        if(!array_key_exists($form->id, $userFormRights)) {
            if($form->public==1) {
                return 1;
            }
            return 0;
        }
        return $userFormRights[$form->id];
    }


    public function show($id, Request $request)
    {
        $form = Form::findOrFail($id);
        $user = Auth::user();
        if(!Auth::check()) {
            return response('Unauthorized.', 401);
        }
        // return response()->json($request->user());
        // return response()->json($user);
        
        $formRight = $this->getFormRight($user, $form);

        if($formRight>0) {
            $form->permission = $formRight;
        } else {
            abort(response()->json(
                "Unauthorized"
            , 401));
        }



        $form->form_elements = $form->form_elements()->orderBy('position', 'asc')->get();
        $form->tags;
        $form->user_observers;
        $form->group_observers;

        $permissions = DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->get();
        $form->setAttribute("permissions", $permissions); 
        return response()->json($form);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $forms = Form::get();

        $tmp = [];
        foreach($forms as $key=>$form) {
            $formRight = $this->getFormRight($user, $form);
            if($formRight>0) {
                $form->permission = $formRight;
                $tmp[] = $form;
            }
        }
        $forms = collect($tmp);
        
        foreach($forms as $form) {
            $form->form_elements;
            $form->tags;
            $form->user_observers;
            $form->group_observers;
        }

        return response()->json($forms);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            "name"=>"required|max:255",
            "deadline"=>"nullable|date",
            "multiple_submissions"=>"required|max:255",
            "elements"=>"required",
            "tags"=>"nullable",
            "public"=>"required",
            "submissions"=>"required",
        ]);

        $form = new Form([
            "name"=>$request->get("name"),
            "deadline"=>$request->get("deadline"),
            "multiple_submissions"=>(bool) $request->get("multiple_submissions"),
            "public"=>(bool) $request->get("public"),
            "submissions"=>(int) $request->get("submissions"),
            "display"=>(bool) filter_var($request->get("display"), FILTER_VALIDATE_BOOLEAN),
        ]);
        if($request->get("submissions")=="1") {
            $permissions = json_decode($request->get("permissions"), true);
            DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->delete();
            foreach($permissions as $permission) {
                $source = $permission["source"];
                $target = $permission["target"];
                $type = $permission["type"]["name"];

                $source_group = Group::where('id', $source['id'])->get()[0];
                $target_group = Group::where('id', $target['id'])->get();
                $target_group = $target_group->mapWithKeys(function ($item, $key) use ($form, $type) {
                    return [$item['id'] => ['permission'=>$type,'form_id'=>$form->id]];
                });
                $source_group->submission_permissions_groups()->attach($target_group);

            }
        }
        $form->creator()->associate(Auth::user())->save();
        
        if($request->get("public")=="0") {
            $form_permissions = json_decode($request->get("form_permissions"), true);
            if($form_permissions)
            $form_permissions_users = collect($form_permissions['users']);
            $form_permissions_users = $form_permissions_users->mapWithKeys(function ($item, $key) {
                return [$item['id'] => ['permission'=>'submit']];
            });
            $form_permissions_groups = collect($form_permissions['groups']);
            $form_permissions_groups = $form_permissions_groups->mapWithKeys(function ($item, $key) {
                return [$item['id'] => ['permission'=>'submit']];
            });
            $form->user_observers()->sync($form_permissions_users);
            $form->group_observers()->sync($form_permissions_groups);
            $form->user_observers;
            $form->group_observers;            
        }


        $elements = json_decode($request->get("elements"), true);
        $tags = json_decode($request->get("tags"), true);


        foreach($elements as $element) {
            $newElement = new FormElement([
                "component"=>$element["component"],
                "position"=>$element["position"],
                "data"=>$element["data"],
            ]);
            $form->form_elements()->save($newElement);
        }
        if($tags) {
            $tagIds = [];
            foreach($tags as $tag) {
                $tag = Tag::where("name", $tag)->get()[0];
                $tagIds[] = $tag->id;
            }

            $form->tags()->sync($tagIds);
        }

        return response()->json($form);
    }


    public function update(Request $request, $id)
    {       
        $form = Form::findOrFail($id);

        $request->validate([
            "name"=>"required|max:255",
            "deadline"=>"nullable|date",
            "multiple_submissions"=>"required|max:255",
            "elements"=>"required",
            "tags"=>"nullable",
            "public"=>"required",
            "submissions"=>"required",
            "display"=>"required",
        ]);

        
        $form->name = $request->get("name");
        $form->deadline = $request->get("deadline");
        $form->multiple_submissions = filter_var($request->get("multiple_submissions"), FILTER_VALIDATE_BOOLEAN);
        $form->public = filter_var($request->get("public"), FILTER_VALIDATE_BOOLEAN);
        $form->submissions = $request->get("submissions");
        $form->display = filter_var($request->get("display"), FILTER_VALIDATE_BOOLEAN);

        if($request->get("submissions")=="1") {
            $permissions = json_decode($request->get("permissions"), true);
            DB::table('agent_group_submissions_permissions')->where("form_id", $form->id)->delete();
            foreach($permissions as $permission) {
                $source = $permission["source"];
                $target = $permission["target"];
                $type = $permission["type"];
                // return response()->json($source);

                // $source_group = Group::where('id', $source['id'])->get()[0];
                // $target_group = Group::where('id', $target['id'])->get();
                $source_group = Group::where('id', $source)->get()[0];
                $target_group = Group::where('id', $target)->get();                
                $target_group = $target_group->mapWithKeys(function ($item, $key) use ($form, $type) {
                    return [$item['id'] => ['permission'=>$type,'form_id'=>$form->id]];
                });
                $source_group->submission_permissions_groups()->attach($target_group);

            }
        }

        
        $elements = json_decode($request->get("elements"), true);
        $elements = collect($elements);
        $del = $form->form_elements->pluck("id")->diff($elements->pluck("id"));
        $update = $form->form_elements->pluck("id")->intersect($elements->pluck("id"));
        $create = $elements->where("id", "");
        
        $form->name = $request->get("name");
        $form->deadline = $request->get("deadline");
        $form->multiple_submissions = (bool) $request->get("multiple_submissions");
    

        foreach($form->form_elements as $form_element) {
            if($del->contains($form_element->id)) {
                $form_element->delete();
            }
            if($update->contains($form_element->id)) {
                $submit_element = $elements->firstWhere("id", $form_element->id);
                $form_element->data = $submit_element["data"];
                $form_element->position = $submit_element["position"];
                $form_element->save();
            }
        }

        
        foreach($create as $submit_element) {
            $newElement = new FormElement([
                "component"=>$submit_element["component"],
                "position"=>$submit_element["position"],
                "data"=>$submit_element["data"],                
            ]);
            $form->form_elements()->save($newElement);
        }
        $form->save();
        return response()->json($form);
    }


    public function destroy($id)
    {
        $form = Form::findOrFail($id);
        foreach($form->form_elements as $form_element) {
            $form_element->delete();
        }
        $form->user_observers()->detach();
        $form->group_observers()->detach();
        $users = User::whereHas('submission_permissions_users', function($query) use($form) {
            $query->where('form_id', '=', $form->id);
        })->get();
        if(!empty($users)) {
            foreach($users as $user) {
                $user->submission_permissions_users()->detach(); 
            }
        }        
        $users = User::whereHas('submission_permissions_groups', function($query) use($form) {
            $query->where('form_id', '=', $form->id);
        })->get();
        if(!empty($users)) {
            foreach($users as $user) {
                $user->submission_permissions_groups()->detach();
            }            
        }        

        $groups = Group::whereHas('submission_permissions_users', function($query) use($form) {
            $query->where('form_id', '=', $form->id);
        })->get();
        if(!empty($groups)) {
            foreach($groups as $group) {
                $group->submission_permissions_users()->detach();
            }            
        }   
        $groups = Group::whereHas('submission_permissions_groups', function($query) use($form) {
            $query->where('form_id', '=', $form->id);
        })->get(); 
        if(!empty($groups)) {
            foreach($groups as $group) {
                $group->submission_permissions_groups()->detach();
            }            
        }                
        
        $form->delete();


        return response()->json(Form::all());
    }

}
