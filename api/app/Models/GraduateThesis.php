<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GraduateThesis extends Model
{
    use HasFactory;


    protected $fillable = [
        'MatNr',
        'Vorname',
        'Nachname',
        'Note',
        'Prüfer',
        'Zweit-Prüfer',
        'Status',
        'Abgabedatum (geplant)',
        'Abgabedatum (erfolgt)',
        'Vortragsdatum',
        'Prüfungsdatum',
        'Nummer',
        'Interne Kennung',
        'Startsemester',
        'E-Mail',
        'Teilleistung',
        'Titel',
        'Titel übersetzt',
        'Sprache',
        'Vergabedatum',
        'Korrekturfrist',
        'Externe Arbeit',
        'Prüfungsbearbeiter',
        'Organisationseinheiten',
    ];

    protected $casts = [
        'Abgabedatum (geplant)' => 'date:d.m.Y',
        'Abgabedatum (erfolgt)' => 'date:d.m.Y',
        'Vortragsdatum' => 'date:d.m.Y',
        'Prüfungsdatum' => 'date:d.m.Y',
        'Vergabedatum' => 'date:d.m.Y',
        'Korrekturfrist' => 'date:d.m.Y',
    ]; 

    public static function parseThesis($thesis, $thesis_instance) {
        $parse_functions = [
            'MatNr'=>function($thesis) {return $thesis['MatNr'];},
            'Vorname'=>function($thesis) {return $thesis['Vorname'];},
            'Nachname'=>function($thesis) {return $thesis['Nachname'];},
            'Note'=>function($thesis) {return $thesis['Note'];},
            'Prüfer'=>function($thesis) {return $thesis['Prüfer'];},
            'Zweit-Prüfer'=>function($thesis) {return $thesis['Zweit-Prüfer'];},
            'Status'=>function($thesis) {return $thesis['Status'];},
            'Abgabedatum (geplant)'=>function($thesis) {
                if(!$thesis['Abgabedatum (geplant)']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $thesis['Abgabedatum (geplant)']);
                return $date;
            },
            'Abgabedatum (erfolgt)'=>function($thesis) {
                if(!$thesis['Abgabedatum (erfolgt)']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $thesis['Abgabedatum (erfolgt)']);
                return $date;
            },
            'Vortragsdatum'=>function($thesis) {
                if(!$thesis['Vortragsdatum']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $thesis['Vortragsdatum']);
                return $date;
            },
            'Prüfungsdatum'=>function($thesis) {
                if(!$thesis['Prüfungsdatum']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $thesis['Prüfungsdatum']);
                return $date;
            },
            'Nummer'=>function($thesis) {return $thesis['Nummer'];},
            'Interne Kennung'=>function($thesis) {return $thesis['Interne Kennung'];},
            'Startsemester'=>function($thesis) {return $thesis['Startsemester'];},
            'E-Mail'=>function($thesis) {return $thesis['E-Mail'];},
            'Teilleistung'=>function($thesis) {return $thesis['Teilleistung'];},
            'Titel'=>function($thesis) {return $thesis['Titel'];},
            'Titel übersetzt'=>function($thesis) {return $thesis['Titel übersetzt'];},
            'Sprache'=>function($thesis) {return $thesis['Sprache'];},
            'Vergabedatum'=>function($thesis) {
                if(!$thesis['Vergabedatum']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $thesis['Vergabedatum']);
                return $date;
            },
            'Korrekturfrist'=>function($thesis) {
                if(!$thesis['Korrekturfrist']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $thesis['Korrekturfrist']);
                return $date;
            },
            'Externe Arbeit'=>function($thesis) {return $thesis['Externe Arbeit'];},
            'Prüfungsbearbeiter'=>function($thesis) {return $thesis['Prüfungsbearbeiter'];},
            'Organisationseinheiten'=>function($thesis) {return $thesis['Organisationseinheiten'];},
        ];
        foreach($parse_functions as $fillable=>$function) {
            $thesis_instance->{$fillable} = $function($thesis);
        }
        return $thesis_instance;
    }

    public function graduate() {
        return $this->belongsTo(Graduate::class, 'MatNr', 'MatrNr');
    }

}
