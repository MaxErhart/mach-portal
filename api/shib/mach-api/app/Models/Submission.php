<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'form_id',
        'confirmed',
        'confirmation_id',
        '_data',
        'is_archived',
        'archive_group',
        'archive_owner',
        'archive_data',
        'archive_label_fallback',
        'archive_element_label',
        'archive_element_hirarchy',
    ];    
    
    protected $casts = [
        'archive_element_hirarchy' => 'array',
        'pivot.data' => 'array',
        '_data' => 'array',
        'archive_element_label' => 'array',
        'archive_label_fallback' => 'array',
        'archive_owner' => 'array',
        'archive_data' => 'array',
        'confirmed' => 'boolean',
    ];

    protected $appends = ["input_data"];

    public function getInputDataAttribute() {
        if($this->is_archived) {
            return $this->archive_data;
        }
        return json_decode($this->attributes['_data']);
    }

    private function flatArchiveData($data,$flat_data=[]) {
        if(!is_array($data)) {
            return $data;
        }
        foreach($data as $id=>$value) {
            $flat_data[] = [$id=>$this->flatArchiveData($value["value"],$flat_data)];
        }
        return $flat_data;
    }
    public function getArchiveData() {
        if(!$this->is_archived) {
            return null;
        }
        $flat_data = [];
        foreach($this->archive_data as $id=>$data) {
            $flat_data[$id] = $this->flatArchiveData($data["value"]);
        }
        return $flat_data;
    }

    public function get_list_of_owners() {
        if($this->owner_type=="App\\Models\\User") {
            return collect([$this->owner]);
        } else if($this->owner_type=="App\\Models\\Group") {
            return $this->owner->users;
        }
    }

    public function getPermission($user) {
        if($this->permission!==NULL) {
            return $this->permission;
        }
        $highest = 0;

        if($this->form->creator_id==$user->id) {
            $this->permission = 3;
            return 3;
        } else if($this->owner_type=="App\\Models\\User" && $this->owner->id==$user->id) {
            $this->permission = 3;
            return 3;
        } else if($user->groups->pluck('id')->contains(25)) {
            $this->permission = 3;
            return 3;
        }

        $permissions = collect([]);
        if($this->owner_type==='App\\Models\\User') {
            $groupIds = $this->owner->groups->pluck('id');
            foreach($user->groups as $group) {
                $permissions = $permissions->merge($group->submission_permissions_groups()->wherePivot('form_id', $this->form->id)->whereIn('group_id', $groupIds)->get());
                $permissions = $permissions->merge($group->submission_permissions_users()->wherePivot('form_id', $this->form->id)->where('user_id',$this->owner_id)->get());
            }
            $permissions = $permissions->merge($user->submission_permissions_groups()->wherePivot('form_id', $this->form->id)->whereIn('group_id', $groupIds)->get());
            $permissions = $permissions->merge($user->submission_permissions_users()->wherePivot('form_id', $this->form->id)->where('user_id',$this->owner_id)->get());
        
        } else {
            foreach($user->groups as $group) {
                $permissions = $permissions->merge($group->submission_permissions_groups()->wherePivot('form_id', $this->form->id)->where('group_id', $this->owner_id)->get());
            }
            $permissions = $permissions->merge($user->submission_permissions_groups()->wherePivot('form_id', $this->form->id)->where('group_id', $this->owner_id)->get());
        }
        foreach($permissions as $permission) {
            if($permission->pivot->permission>$highest) {
                $highest = $permission->pivot->permission;
            }
        }
        $this->permission = $highest;
        return $highest;
    }


    public function actions()
    {
        return $this->morphToMany(Action::class, 'actionable', 'actionable_action');
    }


    public function owner() {
        return $this->morphTo();
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function form() {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function form_elements() {
        return $this->belongsToMany(FormElement::class, 'submissions_element_data', 'submission_id', 'el_id')->withPivot('data');
    }
    
    public function replies() {
        return $this->hasMany(Reply::class);
    }


}
