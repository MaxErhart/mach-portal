<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        "name"
    ];

    public function getUserPermissionOnGroup($user) {
        if($user->isAdmin()) {
            return 3;
        }
        if($user->hasGroup($this->id)) {
            return 3;
        }
        return 0;
    }

    public function get_name() {
        return $this->name;
    }

    public function emails()
    {
        return $this->morphToMany(Email::class, 'recipient', 'email_recipient');
    }

    public function users() {
        return $this->belongsToMany(User::class, 'group_user', 'group_id', 'user_id');
    }

    public function archive_permissions() {
        return $this->morphMany(ArchivePermission::class, 'agent');
    }

    public function submissions() {
        return $this->morphMany(Submission::class, 'owner');
    }

    public function app_permissions() {
        return $this->belongsToMany(App::class, 'group_app_permissions', 'group_id', 'app_id');
    }

    public function settings() {
        return $this->morphToMany(Settings::class, 'agent_setting');
    }

    public function group_app_settings() {
        return $this->hasMany(GroupAppSettings::class);
    }

    public function view_forms() {
        return $this->morphToMany(Form::class, 'agent', 'agent_form_permissions')->withPivot('submit_permission', 'form_permission');
    }
 
    public function submission_permissions_users() {
        return $this->morphToMany(User::class, 'agent', 'agent_user_submissions_permissions')->withPivot('form_id', 'permission');
    }    

    public function submission_permissions_groups() {
        return $this->morphToMany(Group::class, 'agent', 'agent_group_submissions_permissions')->withPivot('form_id', 'permission');
    }

    public function submission_user_observers() {
        return $this->morphedByMany(User::class, 'agent', 'agent_group_submissions_permissions')->withPivot('form_id', 'permission');
    }

    public function submission_group_observers() {
        return $this->morphedByMany(Group::class, 'agent', 'agent_group_submissions_permissions')->withPivot('form_id', 'permission');
    }


}
