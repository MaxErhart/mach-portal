<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntranceExam extends Model
{
    use HasFactory;

    protected $fillable = [
        'term',
        'year',
        'exam_date',
        'exam_time',
        'vorsitz',
        'zeichen',
        'current',
        'degree_de',
        'degree_en',
    ];    

    protected $casts = [
        'exam_date' => 'date:d.m.Y',
        'exam_time' => 'datetime:H:i',
        'deadline' => 'date:d.m.Y',
        'current' => 'boolean',
    ]; 

    public function bewerber() {
        return $this->hasMany(Bewerber::class);
    }

}
