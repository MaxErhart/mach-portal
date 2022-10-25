<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bescheid extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_pdf',
    ];

    public function bewerber() {
        return $this->belongsTo(Bewerber::class, 'bewerber_id');
    }    

}
