<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Graduate extends Model
{
    use HasFactory;

    protected $fillable = [
        'MatrNr',
        'Vorname',
        'Nachname',
        'Version',
        'Status',
        'Immatrikuliert',
        'Letzte Prüfung',
        'Ist-LP',
        'Soll-LP',
        'Studienstart',
        'Studienende',
        'Exma-Datum',
        'Fachsemester',
        'E-Mail',
    ];

    protected $casts = [
        'Letzte Prüfung' => 'date:d.m.Y',
        'Studienstart' => 'date:d.m.Y',
        'Exma-Datum' => 'date:d.m.Y',
    ]; 

    public function syncTheses($theses) {
        foreach($theses as $thesis) {
            $server_theses = GraduateThesis::where('Nummer',$thesis["Nummer"])->where('MatNr',$this->MatrNr)->get();
            if($server_theses->count()>1) {
                abort(response()->json("Multiple theses with identical numbers",400));
            } else if($server_theses->count()==1) {
                if($thesis["server_match"]==2) {
                    continue;
                } else if($thesis["server_match"]==1) {
                    GraduateThesis::parseThesis($thesis,$server_theses[0]);
                    $server_theses[0]->save();
                } else {
                    abort(response()->json("Mismatch between server and uploaded data",400));
                }
            } else {
                if($thesis["server_match"]==0) {
                    $new_thesis = new GraduateThesis();
                    GraduateThesis::parseThesis($thesis,$new_thesis);
                    $new_thesis->save();
                } else {
                    abort(response()->json("Thesis already exists",400));
                }
            }
        }
    }

    public static function parseGraduate($graduate, $graduate_instance) {
        $parse_functions = [
            'MatrNr'=>function($graduate) {return $graduate['MatrNr'];},
            'Vorname'=>function($graduate) {return $graduate['Vorname'];},
            'Nachname'=>function($graduate) {return $graduate['Nachname'];},
            'Version'=>function($graduate) {return $graduate['Version'];},
            'Status'=>function($graduate) {return $graduate['Status'];},
            'Immatrikuliert'=>function($graduate) {return $graduate['Immatrikuliert'];},
            'Letzte Prüfung'=>function($graduate) {
                if(!$graduate['Letzte Prüfung']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $graduate['Letzte Prüfung']);
                return $date;
            },
            'Ist-LP'=>function($graduate) {
                if($graduate['Ist-LP']==="") {
                    return NULL;
                }
                return $graduate['Ist-LP'];
            },
            'Soll-LP'=>function($graduate) {
                return $graduate['Soll-LP'];
            },
            'Studienstart'=>function($graduate) {
                if(!$graduate['Studienstart']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $graduate['Studienstart']);
                return $date;
            },
            'Studienende'=>function($graduate) {return $graduate['Studienende'];},
            'Exma-Datum'=>function($graduate) {
                if(!$graduate['Exma-Datum']) {
                    return NULL;
                }
                $date = Carbon::createFromFormat('d.m.Y', $graduate['Exma-Datum']);
                return $date;
            },
            'Fachsemester'=>function($graduate) {return $graduate['Fachsemester'];},
            'E-Mail'=>function($graduate) {return $graduate['E-Mail'];},
        ];
        foreach($parse_functions as $fillable=>$function) {
            $graduate_instance->{$fillable} = $function($graduate);
        }
        return $graduate_instance;
    }




    public function theses() {
        return $this->hasMany(GraduateThesis::class, 'MatNr', 'MatrNr');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'MatrNr', 'matriculationNumber');
    }


}
