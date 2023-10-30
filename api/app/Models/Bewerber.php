<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bewerber extends Model
{
    use HasFactory;

    protected $fillable = [
        'Geschlecht',
        'Last name',
        'First name',
        'Date of birth',
        'Number',
        'Street and street number',
        'Town',
        'Country',
        'Received on',
        'Email',
        'Status comments for applicants',
        'Name (en)',
        'ILIAS',
        'ergebnis',
        'last_login',
        'entrance_exam_registration_changed',
        'entrance_exam_registered',
        'State',
        'data_protection',
    ];

    protected $casts = [
        'Received on' => 'date:d.m.Y',
        'last_login' => 'date:d.m.Y',
        'Date of birth' => 'date:d.m.Y',
        'entrance_exam_registration_changed' => 'date:d.m.Y',
        'entrance_exam_registered' => 'boolean',
        'data_protection' => 'boolean',
    ];     

    public function entrance_exam() {
        return $this->belongsTo(EntranceExam::class, 'entrance_exam_id');
    }

    public function bescheid() {
        return $this->hasMany(Bescheid::class)->orderBy('updated_at','desc');
    }

}
