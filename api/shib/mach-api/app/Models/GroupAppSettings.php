<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAppSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'app_id',
        'custom_settings',
        'creator_id',
    ];

    protected $casts = [
        'custom_settings' => 'array'
    ];    

    public function groups() {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function settings() {
        return $this->hasMany(Settings::class);
    }
    
    public function apps() {
        return $this->belongsTo(App::class, 'app_id');
    }
}
