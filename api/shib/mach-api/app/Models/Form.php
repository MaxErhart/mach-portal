<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ]; 

    protected $casts = [
        'deadline' => 'datetime:Y-m-d',
        'multiple_submissions' => 'boolean',
        'public' => 'boolean',
        'display' => 'boolean',
    ];    

    public function submissions() {
        return $this->hasMany(Submission::class);
    }
    
    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function form_elements() {
        return $this->hasMany(FormElement::class);
    }    

    public function tags() {
        return $this->belongsToMany(Tag::class, 'form_tag', 'form_id', 'tag_id');
    }

    public function user_observers() {
        return $this->morphedByMany(User::class, 'agent', 'agent_form_permissions')->withPivot('permission');
    }

    public function group_observers() {
        return $this->morphedByMany(Group::class, 'agent', 'agent_form_permissions')->withPivot('permission');
    }

}
