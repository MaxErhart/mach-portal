<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'shib_id',
        'firstname',
        'lastname',
        'email',
        'password',
        'affiliation',
        'degree',
        'degreeText',
        'fieldOfStudy',
        'fieldOfStudyId',
        'fieldOfStudyText',
        'matriculationNumber',
        'address_street',
        'address_city',
        'address_country',
        'address_postalcode',
        'private_email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'affiliation' => 'array'
    ];

    /**
     * The groups that belong to the user.
     */
    public function groups() {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id');
    }

    public function rightsOnApps() {
        $apps = [];
        foreach($this->groups as $group) {
            foreach($group->app_permissions as $app) {
                $apps[] = $app;
            }
        }
        $this->rightsOnApps = collect($apps);
        return $apps;
    }

    public function hasGroup($groupId) {
        foreach(Auth::user()->groups as $group) {
            if($group['id']==$groupId) {
                return true;
            }
        }
        return false;
    }

    public function submissions() {
        return $this->hasMany(Submission::class);
    }

    public function apps() {
        return $this->hasMany(App::class);
    }

    public function tags() {
        return $this->hasMany(Tag::class);
    }

    public function forms() {
        return $this->hasMany(Form::class);
    }
    
    public function settings() {
        return $this->morphToMany(Settings::class, 'agent_setting');
    }

    public function stundenzettel() {
        return $this->hasMany(Stundenzettel::class);
    }

    public function view_forms() {
        return $this->morphToMany(Form::class, 'agent', 'agent_form_permissions')->withPivot('permission');
    }


    public function submission_permissions_users() {
        return $this->morphToMany(User::class, 'agent', 'agent_user_submissions_permissions')->withPivot('form_id', 'permission');
    }

    public function submission_user_observers() {
        return $this->morphedByMany(User::class, 'agent', 'agent_user_submissions_permissions')->withPivot('form_id', 'permission');
    }

    public function submission_group_observers() {
        return $this->morphedByMany(Group::class, 'agent', 'agent_user_submissions_permissions')->withPivot('form_id', 'permission');
    }

    public function submission_permissions_groups() {
        return $this->morphToMany(Group::class, 'agent', 'agent_group_submissions_permissions')->withPivot('form_id', 'permission');
    }


    
}
