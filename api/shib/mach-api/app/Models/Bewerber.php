<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bewerber extends Model
{
    use HasFactory;

    protected $fillable = [
        'Geschlecht',
        'Name',
        'Vorname',
        'Geboren',
        'Bewerbungs-nummer',
        'Adresse',
        'Ort',
        'Land',
        'Datum Antrag',
        'KIT-E-Mail',
        'Bemerkung',
        'Studiengang',
        'ILIAS',
        'Ergebnis',
        'last_login',
        'entrance_exam_registration_changed',
        'entrance_exam_registered',
        'status',
        'data_protection',
    ];

    protected $casts = [
        'Datum Antrag' => 'date:d.m.Y',
        'last_login' => 'date:d.m.Y',
        'Geboren' => 'date:d.m.Y',
        'entrance_exam_registration_changed' => 'date:d.m.Y',
        'entrance_exam_registered' => 'boolean',
        'data_protection' => 'boolean',
    ];     

    public function entrance_exam() {
        return $this->belongsTo(EntranceExam::class, 'entrance_exam_id');
    }

    public function bescheid() {
        return $this->hasMany(Bescheid::class);
    }

}
