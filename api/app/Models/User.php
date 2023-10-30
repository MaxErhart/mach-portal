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

    protected $appends = ["name"];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'affiliation' => 'array'
    ];

    public function getNameAttribute() {
        return $this->firstname." ".$this->lastname;
    }

    public static function getUserByName($name) {
        $name_fragments = explode(' ', $name);
        for($i=0; $i<count($name_fragments); $i++) {
            for($j=$i+1; $j<count($name_fragments); $j++) {
                $firstname = $name_fragments[$i];
                $lastname = $name_fragments[$j];
                $user = User::where('firstname',$firstname)->where('lastname',$lastname)->first();
                if($user) {
                    return $user;
                }
            }
        }

    }

    public function getUserPermissionOnUser($user) {
        if($user->isAdmin()) {
            return 3;
        }
        if($this->id===$user->id) {
            return 3;
        }
        return 0;
    }


    public function getUserFormRights() {
        if($this->userFormRights!==NULL) {
            return $this->userFormRights;
        }
        $forms = $this->view_forms()->get();
        foreach($this->groups as $group) {
            $forms = $forms->concat($group->view_forms()->get());
        }
        $formsRightsDict = [];
        foreach($forms as $form) {
            if(array_key_exists($form->id, $formsRightsDict)) {
                if($formsRightsDict[$form->id]['submit_permission']<$form->pivot->submit_permission) {
                    $formsRightsDict[$form->id]['submit_permission'] = $form->pivot->submit_permission;
                } else if($formsRightsDict[$form->id]['form_permission']<$form->pivot->form_permission) {
                    $formsRightsDict[$form->id]['form_permission'] = $form->pivot->form_permission;
                }
            } else {
                $formsRightsDict[$form->id] = [
                    'submit_permission'=>$form->pivot->submit_permission,
                    'form_permission'=>$form->pivot->form_permission,
                ];
            }
        }
        $this->userFormRights = $formsRightsDict;
        return $formsRightsDict;
    }

    /**
     * The groups that belong to the user.
     */
    public function groups() {
        return $this->belongsToMany(Group::class, 'group_user', 'user_id', 'group_id');
    }

    public function get_name() {
        return $this->firstname." ".$this->lastname;
    }

    public function actions() {
        return $this->hasMany(Action::class);
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

    public function isAdmin() {
        if($this->hasGroup(25)) {
            return true;
        }
        return false;
    }

    public function hasGroup($groupId) {
        foreach($this->groups as $group) {
            if($group->id==$groupId) {
                return true;
            }
        }
        return false;
    }
    public function emails()
    {
        return $this->morphToMany(Email::class, 'recipient', 'email_recipient');
    }

    public function archive_permissions() {
        return $this->morphMany(ArchivePermission::class, 'agent');
    }

    public function submissions() {
        return $this->morphMany(Submission::class, 'owner');
    }

    public function replies() {
        return $this->hasMany(Reply::class);
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


    public function archive_permissions_condensed() {
        $permissions = $this->archive_permissions;
        foreach($this->groups as $group) {
            $permissions = $permissions->merge($group->archive_permissions);
        }

        $levels = [];
        foreach($permissions as $permission) {
            $dir = explode("/",$permission->directory);
            $level = count($dir);
            if($permission->directory==="/") {
                $level = 1;
            }


            if(!array_key_exists($level, $levels)) {
                $levels[$level] = [];
            }
            // return [$permission,$levels,$level];
            if((array_key_exists($permission->directory, $levels[$level]) && $levels[$level][$permission->directory]<$permission->permission) || !array_key_exists($permission->directory, $levels[$level])) {
                $levels[$level][$permission->directory] = $permission->permission;
            }
        }
        // return $levels;

        $removes = [];
        foreach($levels as $level=>$dirs) {
            if($level===1) {
                continue;
            }
            foreach($dirs as $dir=>$permission) {
                if(array_key_exists(1,$levels) && $levels[1]["/"]>=$permission) {
                    $removes[] = [$level,$dir,$permission];
                }
                for($i=2;$i<$level;$i++) {
                    $level_fragment = implode("/",(array_slice(explode("/",$dir),0,$i)));
                    if(array_key_exists($level_fragment, $levels[$i]) && $levels[$i][$level_fragment]>=$permission) {
                        $removes[] = [$level,$dir,$permission];
                    }
                }
            }
        }
        foreach($removes as $remove) {
            unset($levels[$remove[0]][$remove[1]]);
        }
        foreach($levels as $level=>$dirs) {
            if(count($dirs)===0) {
                unset($levels[$level]);
            }
        }
        ksort($levels);
        return array_values($levels);
        // return $levels;
        // foreach($permissions as $permission) {
        //     if($permission->dir==="/") {
        //         if(array_key_exists("/",$condensed) && $permission->permission>$condensed["/"]) {
        //             $condensed["/"] = $permissions->permission;
        //         }
        //         continue;
        //     }

        //     $fragments = explode("/",ltrim(rtrim($permission->dir,"/"),"/"));
        //     $dir_progression = "/";
        //     if(array_key_exists($dir_progression,$condensed) && $condensed[$dir_progression]>$permission->permission) {
        //         continue;
        //     }
        //     foreach($fragments as $fragment) {
        //         $dir_progression .= $fragment
        //         if(array_key_exists($parent_dir.$fragment, $condensed) && $condensed[]) [

        //         ]
        //     }

        // }

        return $permissions;

    }

    public function archive_permissions_condensed2() {
        $permissions = $this->archive_permissions;
        foreach($this->groups as $group) {
            $permissions = $permissions->merge($group->archive_permissions);
        }
        $condensed = [];
        $lowest = 99;
        foreach($permissions as $permission) {
            $dir = $permission->directory;
            $dir = rtrim($dir, "/");
            $dir = ltrim($dir, "/");
            if($dir=="") {
                $count = 0;
            } else {
                $fragments = explode("/",$dir); 
                $count = count($fragments);
            }
            if($lowest>$count) {
                $lowest = $count;
            }
            if(!array_key_exists($count,$condensed)) {
                $condensed[$count] = [];
            }
            $condensed[$count][] = $permission;
        }
        $condensed = $condensed;
        if(array_key_exists($lowest, $condensed)) {
            // $remove_indices = [];
            // foreach($condensed[$lowest] as $index_1=>$permission_1) {
            //     foreach($condensed[$lowest] as $index_2=>$permission_2) {
            //         $dir_1 = ltrim(rtrim($permission_1, "/"), "/");
            //         $dir_2 = ltrim(rtrim($permission_2, "/"), "/");
            //         if($dir_1===$dir_2) {
            //             if($permission_1->permission>$permission_2->permission) {
            //                 $remove_indices[] = $index_2;
            //             } else {
            //                 $remove_indices[] = $index_1;
            //             }
            //         }
            //     }
            // }
            // foreach($remove_indices as $index) {
            //     unset($condensed[$lowest][$index]);
            // }
            return $condensed[$lowest];
        }
        abort(response()->json(["message"=>"No permission"], 403));
    }


    public function view_forms() {
        return $this->morphToMany(Form::class, 'agent', 'agent_form_permissions')->withPivot('submit_permission', 'form_permission','permission');
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

    public function graduate()
    {
        return $this->hasOne(Graduate::class, 'MatrNr', 'matriculationNumber');
    }
    
}
