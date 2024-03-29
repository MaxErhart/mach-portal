<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lehrveranstaltung extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
    ];

}
