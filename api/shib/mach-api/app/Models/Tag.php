<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        'creator_id',
    ];

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function forms() {
        return $this->belongsToMany(Form::class, 'form_tag', 'tag_id', 'form_id');
    }
    
}
