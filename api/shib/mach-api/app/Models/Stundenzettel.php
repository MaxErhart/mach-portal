<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stundenzettel extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'personal_nummer',
        'stundensatz',
        'vereinbarte_arbeitszeit',
        'institut',
    ];

    public function arbeitstage() {
        return $this->hasMany(StundenzettelArbeitstag::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }    

}
