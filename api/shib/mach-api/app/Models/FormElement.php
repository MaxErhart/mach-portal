<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormElement extends Model
{
    use HasFactory;
    protected $fillable = [
        'form_id',
        'component',
        'position',
        'data',
    ];  

    protected $casts = [
        'data' => 'array'
    ];

    // public function newPivot(Model $parent, array $attributes, $table, $exists) {
    //     return new \App\SubmissionElementPivot($parent, $attributes, $table, $exists);
    // }    

    public function form() {
        return $this->belongsTo(Form::class, 'form_id');
    }
    
    public function submission() {
        return $this->belongsToMany(Submission::class, 'submissions_element_data', 'el_id', 'submission_id');
    }
    
}
