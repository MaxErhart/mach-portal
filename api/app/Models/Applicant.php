<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'sex',
        'firstname',
        'lastname',
        'email',
        'date_of_birth',
        'street',
        // 'street_number',
        'zipcode',
        'city',
        'country',
        'application_number',
        'applicant_number',
        'degree',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
    ];  

}
