<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'creator_id',
    ];

    public function groups_have_permission() {
        return $this->belongsToMany(Group::class, 'group_app_permissions', 'app_id', 'group_id');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function group_app_settings() {
        return $this->hasMany(GroupAppSettings::class);
    }
}
