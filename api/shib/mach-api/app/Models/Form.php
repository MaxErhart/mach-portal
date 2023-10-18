<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use DB;

class Form extends Model
{
    use HasFactory;
    protected $fillable = [
        'creator_id',
        'name',
        'deadline',
        'multiple_submissions',
        'public',
        'view_submissions',
        'edit_submissions',
        'display',
        'submissions',
        'bescheid_common_el_id',
        'bescheid_common_data',
        'bescheide',
        'info_subject',
        'info_body',
        'info_footer',
        'no_login',
        'archive_groups',
        'organizations_regex',
        'organizations_proxy',
    ]; 

    protected $casts = [
        'organizations_regex' => 'array',
        'archive_groups' => 'array',
        'bescheide' => 'array',
        'bescheid_common_data' => 'array',
        'deadline' => 'datetime:Y-m-d',
        'multiple_submissions' => 'boolean',
        'public' => 'boolean',
        'display' => 'boolean',
        'no_login' => 'boolean',
        'organizations_proxy' => 'boolean',
    ];    

    public static function getForm($form_id, $user, $min_permission=1) {
        $form = Form::findOrFail($form_id);
        $permission = $form->getFormPermission($user);
        if($permission<$min_permission) {
            abort(response()->json([
                "message"=>"Unauthorized",
            ], 401));
        }
        $form->permission = $permission;
        $form->form_bescheid_settings;
        return $form;
    }

    private function changeElementDataFormat($element) {
        if($element->component!=='SelectElement' || !array_key_exists('data',$element->data)) {
            return $element;
        }
        $element_data = $element->data;
        $options = [];
        foreach($element->data['data'] as $index=>$option) {
            if(gettype($option)==='array' && array_key_exists('id', $option)) {
                $options[$option['id']] = $option['name'];
            } else {
                $options[$index] = $option;
            }
        }
        $element_data['data'] = $options;
        $element->data = $element_data;

        return $element;
    }


    public $reference_chains = [];
    public function resolveReferences($element,$reference_elements, &$main=[], $prefix=[]) {
        if(array_key_exists($element->id,$this->reference_chains)) {
            return $this->reference_chains[$element->id];
        }
        $new_prefix = [...$prefix,$element->id];

        if($element->component!=="SelectReferenceElement") {
            $main[] = $new_prefix;
            return $main;
        }

        foreach($element->data["formElementIds"] as $el_id) {
            $reference_element = $reference_elements[$el_id];
            $this->resolveReferences($reference_element,$reference_elements,$main, $new_prefix);
        }

        $this->reference_chains[$element->id] = $main;
        return $main;

    }


    public function getFormElementsIncludingReferencesRecursively($veto=[]) {
        // $elements = collect([]);
        $elements = [];
        foreach($this->base_form_elements as $form_element) {
            if(!array_key_exists($form_element->form_id, $elements)) {
                $elements[$form_element->form_id] = [];
            }
            // $elements->put($form_element->id, $form_element);
            $elements[$form_element->form_id][$form_element->id] = $form_element;
            if($form_element->input==0) {
                continue;
            }
            if($form_element->component!=='SelectReferenceElement') {
                continue;
            }
            if(in_array($form_element->data['formId'],$veto)) {
                continue;
            }
            $reference_form = Form::findOrFail($form_element->data['formId']);
            $reference_elements = $reference_form->getFormElementsIncludingReferencesRecursively([$this->id,...$veto]);
            foreach($reference_elements as $reference_element_by_id) {
                // $elements->put($reference_element->id, $reference_element);
                foreach($reference_element_by_id as $reference_element) {
                    $elements[$reference_element->form_id][$reference_element->id] = $reference_element;
                }
            }
        }
        // foreach($elements as $index=>$element) {
        //     $elements[$index] = $this->changeElementDataFormat($element);
        // }
        return $elements;
    }


    public function getFormElementsAndSubmissionsIncludingReferencesRecursively($user,$veto=[],$first=true) {
        // $elements = collect([]);
        $elements = [];
        if($first) {
            $submissions = [$this->id=>$this->getFormSubmissions($user)];
        } else {
            $submissions = [$this->id=>$this->getFormSubmissionsBoth($user)];
        }
        foreach($this->base_form_elements as $form_element) {
            if(!array_key_exists($form_element->form_id, $elements)) {
                $elements[$form_element->form_id] = [];
            }
            // $elements->put($form_element->id, $form_element);
            $elements[$form_element->form_id][$form_element->id] = $form_element;
            if($form_element->input==0) {
                continue;
            }
            if($form_element->component!=='SelectReferenceElement') {
                continue;
            }
            if(in_array($form_element->data['formId'],$veto)) {
                continue;
            }
            $reference_form = Form::findOrFail($form_element->data['formId']);
            $data = $reference_form->getFormElementsAndSubmissionsIncludingReferencesRecursively($user,[$this->id,...$veto],false);
            $elements += $data[0];
            foreach($data[0] as $form_id=>$reference_element_by_form_id) {
                // $elements->put($reference_element->id, $reference_element);
                foreach($reference_element_by_form_id as $reference_element) {
                    $elements[$form_id][$reference_element->id] = $reference_element;
                }
            }
            $submissions = $submissions + $data[1];

        }
        // foreach($elements as $elements_by_form) {
        //     foreach($elements_by_form as $element) {
        //         $element = $this->changeElementDataFormat($element);
        //     }
        // }
        return [$elements,$submissions];
    }

    // public function getFormSubmissionPermissions($user) {
    //     $groups = [];
    //     $user_group = DB::table('agent_group_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\User','agent_id'=>$user->id])->get();
    //     $group_group = DB::table('agent_group_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\Group'])->whereIn('agent_id',$user->groups->pluck('id'))->get();
    //     foreach($user_group as $permission) {
    //         if(array_key_exists($permission->group_id, $groups) && $groups[$permission->group_id]>=$permission->permission) {
    //             continue;
    //         }
    //         $groups[$permission->group_id] = $permission->permission;
    //     }
    //     foreach($group_group as $permission) {
    //         if(array_key_exists($permission->group_id, $groups) && $groups[$permission->group_id]>=$permission->permission) {
    //             continue;
    //         }
    //         $groups[$permission->group_id] = $permission->permission;
    //     }
    // }


    public function getArchiveSubmissions($user,$key=null) {


        if($key==null) {
            $archive_submissions = Submission::where('form_id',$this->id)->where('is_archived',true)->get();
        } else {
            $archive_submissions = Submission::where('form_id',$this->id)->where('is_archived',true)->where('archive_group',$key)->get();
        }

        if($user->isAdmin()) {
            foreach($archive_submissions as $submission) {
                $submission->permission = 3;
                foreach($submission->replies as $reply) {
                    $reply->user;
                }
                $submission->archive_owner;
            }
            return $archive_submissions;
        }


        $groups = [];
        $user_group = DB::table('agent_group_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\User','agent_id'=>$user->id])->get();
        $group_group = DB::table('agent_group_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\Group'])->whereIn('agent_id',$user->groups->pluck('id'))->get();
        foreach($user_group as $permission) {
            if(array_key_exists($permission->group_id, $groups) && $groups[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $groups[$permission->group_id] = $permission->permission;
        }
        foreach($group_group as $permission) {
            if(array_key_exists($permission->group_id, $groups) && $groups[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $groups[$permission->group_id] = $permission->permission;
        }

        $users = [];
        $group_models = Group::whereIn('id',array_keys($groups))->get();
        foreach($group_models as $model) {
            foreach($model->users->pluck('id') as $id) {
                if(array_key_exists($id, $users) && $users[$id]>=$groups[$model->id]) {
                    continue;
                }
                $users[$id] = $groups[$model->id];
            }
        }

        $user_user = DB::table('agent_user_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\User','agent_id'=>$user->id])->get();
        foreach($user_user as $permission) {
            if(array_key_exists($permission->group_id, $users) && $users[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $users[$permission->group_id] = $permission->permission;
        }
        $group_user = DB::table('agent_user_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\Group'])->whereIn('agent_id',$user->groups->pluck('id'))->get();
        foreach($group_user as $permission) {
            if(array_key_exists($permission->group_id, $users) && $users[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $users[$permission->group_id] = $permission->permission;
        }

        if(!array_key_exists($user->id,$users) || $users[$user->id]<$this->submissions) {
            $users[$user->id] = $this->submissions;
        }
        // return $users;

        foreach($archive_submissions as $key=>$archive_submission) {
            $perm = 0;
            if($archive_submission->archive_owner_type==='App\\Models\\User' && array_key_exists($archive_submission->archive_owner["id"],$users)) {
                $perm_ = $users[$archive_submission->archive_owner["id"]];
                if($perm_>$perm) {
                    $perm = $perm_;
                }
            }
            if($archive_submission->archive_owner_type=='App\\Models\\Group' && array_key_exists($archive_submission->archive_owner["id"],$groups)) {
                $perm_ = $groups[$archive_submission->archive_owner["id"]];
                if($perm_>$perm) {
                    $perm = $perm_;
                }
            }
            if($archive_submission->archive_owner_type==='App\\Models\\User') {
                foreach($archive_submission->archive_owner["groups"] as $group) {
                    if(array_key_exists($group["id"],$groups)) {
                        $perm_ = $groups[$group["id"]];
                        if($perm_>$perm) {
                            $perm = $perm_;
                        }
                    }
                }
            }

            if($perm<1) {
                unset($archive_submissions[$key]);
                continue;
            }
            $archive_submission->permission = $perm;
            $archive_submission->archive_owner;
            foreach($archive_submission->replies as $reply) {
                $reply->user;
            }
        }
        return $archive_submissions->values();
    }

    public function getFormSubmissionsBoth($user) {
        $live_submissions = $this->getFormSubmissions($user);
        $archive_submissions = $this->getArchiveSubmissions($user);
        return $live_submissions->merge($archive_submissions)->sortBy('updated_at')->values();
    }

    public function getFormSubmissions($user) {

        if($user->isAdmin()) {
            $submissions = Submission::where('form_id',$this->id)->where('is_archived',false)->get();;
            foreach($submissions as $submission) {
                $submission->permission = 3;
                foreach($submission->replies as $reply) {
                    $reply->user;
                }
                $submission->owner->groups;
            }
            return $submissions;
        }

        $groups = [];
        $user_group = DB::table('agent_group_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\User','agent_id'=>$user->id])->get();
        $group_group = DB::table('agent_group_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\Group'])->whereIn('agent_id',$user->groups->pluck('id'))->get();
        foreach($user_group as $permission) {
            if(array_key_exists($permission->group_id, $groups) && $groups[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $groups[$permission->group_id] = $permission->permission;
        }
        foreach($group_group as $permission) {
            if(array_key_exists($permission->group_id, $groups) && $groups[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $groups[$permission->group_id] = $permission->permission;
        }

        $users = [];
        $group_models = Group::whereIn('id',array_keys($groups))->get();
        foreach($group_models as $model) {
            foreach($model->users->pluck('id') as $id) {
                if(array_key_exists($id, $users) && $users[$id]>=$groups[$model->id]) {
                    continue;
                }
                $users[$id] = $groups[$model->id];
            }
        }

        $user_user = DB::table('agent_user_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\User','agent_id'=>$user->id])->get();
        foreach($user_user as $permission) {
            if(array_key_exists($permission->group_id, $users) && $users[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $users[$permission->group_id] = $permission->permission;
        }
        $group_user = DB::table('agent_user_submissions_permissions')->where(['form_id'=>$this->id,'agent_type'=>'App\\Models\\Group'])->whereIn('agent_id',$user->groups->pluck('id'))->get();
        foreach($group_user as $permission) {
            if(array_key_exists($permission->group_id, $users) && $users[$permission->group_id]>=$permission->permission) {
                continue;
            }
            $users[$permission->group_id] = $permission->permission;
        }

        if(!array_key_exists($user->id,$users) || $users[$user->id]<$this->submissions) {
            $users[$user->id] = $this->submissions;
        }
        

        $user_submissions = Submission::where('form_id',$this->id)->where('is_archived',false)->where(function ($query) use ($users){
            $query->where(['owner_type'=>'App\\Models\\User'])->whereIn('owner_id',array_keys($users));
        })->get();

        $group_submissions = Submission::where('form_id',$this->id)->where('is_archived',false)->where(function ($query) use ($groups) {
            $query->where('owner_type','App\\Models\\Group')->whereIn('owner_id',array_keys($groups));
        })->get();

        foreach($user_submissions as $submission) {
            $submission->permission = $users[$submission->owner_id];
            $submission->owner;
            foreach($submission->replies as $reply) {
                $reply->user;
            }
        }
        foreach($group_submissions as $submission) {
            $submission->permission = $groups[$submission->owner_id];
            $submission->owner;
            foreach($submission->replies as $reply) {
                $reply->user;
            }
        }
        return $user_submissions->merge($group_submissions);
    }


    public function getSubmissions($user,$veto=[]) {
        // dd($user);
        $submissions = Submission::where('form_id', $this->id)->get();
        $references = $this->getReferences($user);
        $tmp = [];
        foreach($submissions as $submission) {
            $subRight = $submission->getPermission($user);
            if($subRight>0) {
                $submission->data = $this->getSubmissionData($submission, $user,$veto);
                $tmp[] = $submission;
            }
        }
        $submissions = collect($tmp);
        return $submissions;
    }

    public function getReferences($user,$veto=[]) {
        $references = [];
        $veto_copy = $veto;
        foreach($this->base_form_elements as $form_element) {
            if($form_element->component!=='SelectReferenceElement') {
                continue;
            }
            if(in_array($form_element->data['formId'],$veto) || array_key_exists($form_element->data['formId'], $references)) {
                continue;
            }
            $veto_copy[] = $form_element->data['formId'];
            $reference_form = Form::findOrFail($form_element->data['formId']);
            $reference_form->form_elements;
            $reference_submissions = [];
            $submissions = $reference_form->getSubmissions($user,$veto_copy);
            foreach($submissions as $submission) {
                $reference_submissions[$submission->id] = $submission;
            }
            $references[$reference_form->id] = ['submissions'=>$reference_submissions, 'form'=>$reference_form];
        }
        return $references;
    }

    private function getReference($form_element,$user,$veto=[]) {
        if($form_element->component!=='SelectReferenceElement') {
            return [];
        }
        if($this->references===NULL) {
            $this->references = $this->getReferences($user,$veto);
        }
        return $this->references[$form_element->data['formId']];
    }

    private function getSubmissionData($submission,$user,$veto=[]) {
        $data = [];
        foreach($this->base_form_elements as $form_element) {
            if($form_element->input==0) {
                continue;
            }
            if(!array_key_exists($form_element->id,$submission->_data) || $submission->_data[$form_element->id]===NULL) {
                $data[$form_element->id] = [
                    'label'=>$form_element->data['label'],
                    'data'=>NULL,
                ];
                continue;
            }
            if($form_element->component==='SelectReferenceElement') {
                $reference = $this->getReference($form_element,$user,$veto);
                if(!array_key_exists($submission->_data[$form_element->id], $reference['submissions'])) {
                    $data[$form_element->id] = [
                        'label'=>$form_element->data['label'],
                        'data'=>NULL,
                    ];
                    continue;
                }
                $data[$form_element->id] = [
                    'label'=>$form_element->data['label'],
                    'data'=>$reference['submissions'][$submission->_data[$form_element->id]],
                ];
            } else if($form_element->component==='SelectElement') {
                $option_id = $submission->_data[$form_element->id];
                $data[$form_element->id] = [
                    'label'=>$form_element->data['label'],
                    'data'=>collect($form_element->data['data'])->first(function ($option) use($option_id) {
                        return $option['id']===$option_id;
                    }),
                ];
            } else {
                $data[$form_element->id] = [
                    'label'=>$form_element->data['label'],
                    'data'=>$submission->_data[$form_element->id],
                ];
            }
        }
        return $data;
    }

    public function actions()
    {
        return $this->morphToMany(Action::class, 'actionable', 'actionable_action');
    }


    public function getSubmitPermission($user) {
        if($this->creator_id==$user->id) {
            return 3;
        }
        if($user->groups->pluck('id')->contains(25)) {
            return 3;
        }
        if($this->display==0) {
            return 0;
        }
        $userFormRights = $user->getUserFormRights();
        if(!array_key_exists($this->id, $userFormRights)) {
            if($this->public==1) {
                return 2;
            }
            return 0;
        }
        return $userFormRights[$this->id]['submit_permission'];
    }

    // $user_submissions = Submission::where('form_id',$this->id)->where(function ($query) use ($users){
    //     $query->where(['owner_type'=>'App\\Models\\User'])->whereIn('owner_id',array_keys($users));
    // })->get();

    public function getFormPermission($user) {
        if(!$user && $this->no_login) {
            return 2;
        }
        if(!$user) {
            abort(response()->json([
                "message"=>"Not logged in",
            ], 403));
            return 0;
        }
        if($this->creator_id==$user->id) {
            return 4;
        }
        if($user->groups->pluck('id')->contains(25)) {
            return 4;
        }
        $user_form_permissions = DB::table('agent_form_permissions')->where('form_id',$this->id)->where(function ($query) use($user){
            $query->where(['agent_type'=>'App\\Models\\User', 'agent_id'=>$user->id])->orWhere(function ($query2) use($user) {
                $query2->where('agent_type','App\\Models\\Group')->whereIn('agent_id',$user->groups->pluck('id')->toArray());
            });
        })->get();
        $highest = 0;
        foreach($user_form_permissions as $permission) {
            // BACKWARDS FIX
            $tmp_permission = 0;
            if($permission->form_permission==1) {
                $tmp_permission = 1;
            }
            if($permission->submit_permission==1) {
                $tmp_permission = 1;
            }
            if($permission->submit_permission==2) {
                $tmp_permission = 2;
            }
            if($permission->form_permission==2) {
                $tmp_permission = 3;
            }
            if($permission->form_permission==3) {
                $tmp_permission = 4;
            }
            if($permission->permission>$tmp_permission) {
                $tmp_permission=$permission->permission;
            }
            if($tmp_permission>$highest) {
                $highest = $tmp_permission;
            }
        }
        if(!$this->display && $highest<3) {
            return 0;
        }
        if($this->public && $highest==0) {
            return 2;
        }
        return $highest;
    }


    public function form_submissions() {
        return $this->hasMany(Submission::class);
    }
    
    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function base_form_elements() {
        return $this->hasMany(FormElement::class);
    }
    
    public function form_bescheid_settings() {
        return $this->hasMany(FormBescheidSettings::class);
    } 

    public function tags() {
        return $this->belongsToMany(Tag::class, 'form_tag', 'form_id', 'tag_id');
    }

    public function user_observers() {
        return $this->morphedByMany(User::class, 'agent', 'agent_form_permissions')->withPivot('submit_permission', 'form_permission', 'permission');
    }

    public function group_observers() {
        return $this->morphedByMany(Group::class, 'agent', 'agent_form_permissions')->withPivot('submit_permission', 'form_permission', 'permission');
    }

}
