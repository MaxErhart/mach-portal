<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_app_setting_id',
        'type',
        'unrestricted',
    ];

    public function groupAppSettings() {
        return $this->belongsTo(GroupAppSettings::class, 'group_app_settings_id');
    }

    public function users() {
        return $this->morphedByMany(User::class, 'agent_settings');
    }

    public function groups() {
        return $this->morphedByMany(Group::class, 'agent_settings');
    }


}
