<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'subject',
        'content',
        'files',
        'seen',
    ];


    protected $casts = [
        'files' => 'array',
        'seen' => 'array',
        'created_at'=>'date:d.m.Y H:i'
    ];

    public function submission() {
        return $this->belongsTo(Submission::class);
    }

    public function actions()
    {
        return $this->morphToMany(Action::class, 'actionable', 'actionable_action');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
