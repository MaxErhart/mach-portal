<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    use HasFactory;

    protected $table = 'archive';

    protected $fillable = [
        'file',
        'tags',
        'description',
        'contents',
        'fragment',
        'file_hash',
    ];


    protected $casts = [
        'file' => 'array',
    ];


    public function creator() {
        return $this->belongsTo(User::class);
    }

    public function search_engine() {
        return $this->hasMany(ArchiveSe::class);
    }

        
}
