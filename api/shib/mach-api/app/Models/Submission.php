<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'form_id',
    ];    

    protected $casts = [
        'pivot.data' => 'array',
    ];

    // public function newPivot(Model $parent, array $attributes, $table, $exists) {
    //     return new \App\SubmissionElementPivot($parent, $attributes, $table, $exists);
    // }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function form() {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function form_elements() {
        return $this->belongsToMany(FormElement::class, 'submissions_element_data', 'submission_id', 'el_id')->withPivot('data');
    }
    
}
