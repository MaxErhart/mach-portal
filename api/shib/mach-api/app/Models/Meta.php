<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_on',
        'maintenance_enddate',
        'maintenance_message',
    ];

    protected $casts = [
        'maintenance_on' => 'boolean',
        'maintenance_enddate' => 'date:d.m.Y',
    ];

    public function setMaintenanceOnAttribute($value)
    {
        $this->attributes['maintenance_on'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

}
