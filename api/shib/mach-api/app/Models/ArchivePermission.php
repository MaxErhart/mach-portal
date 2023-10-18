<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'directory',
        'permission',
    ];

    public function agent() {
        return $this->morphTo();
    }

}
