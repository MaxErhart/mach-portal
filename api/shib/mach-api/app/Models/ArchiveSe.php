<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveSe extends Model
{
    use HasFactory;

    protected $table = 'archive_se';
    public $timestamps = false;
    protected $fillable = [
        'word',
        'count',
        'word_offset',
    ];

    public function archive() {
        return $this->belongsTo(Archive::class);
    }

    public function next()
    {
        return $this->belongsToMany(ArchiveSe::class, 'archive_se_order', 'current_id', 'next_id');
    }

    public function previous()
    {
        return $this->belongsToMany(ArchiveSe::class, 'archive_se_order', 'next_id', 'current_id');
    }

}
