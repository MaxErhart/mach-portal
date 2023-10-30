<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'exam_date',
        'exam_time',
        'lehrveranstaltung_id',
    ];    

    protected $casts = [
        'exam_date' => 'date:d.m.Y',
        'exam_time' => 'datetime:H:i',
    ];     
}
