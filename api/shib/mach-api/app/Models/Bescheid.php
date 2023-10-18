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
        'file',
    ];

    protected $casts = [
        'updated_at'=>'date:d.m.Y',
        'file' => 'array',
    ]; 

    public function bewerber() {
        return $this->belongsTo(Bewerber::class, 'bewerber_id');
    }    

}
