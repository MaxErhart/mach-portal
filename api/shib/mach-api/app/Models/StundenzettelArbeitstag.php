<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StundenzettelArbeitstag extends Model
{
    use HasFactory;

    protected $fillable = [
        'stundenzettel_id',
        'start',
        'end',
        'task',
        'vacation_millsec',
    ];

    public function stundenzettel() {
        return $this->belongsTo(Stundenzettel::class);
    }


}
