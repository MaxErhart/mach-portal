<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormBescheidSettings extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'form_id',
        'form_id_containing_bescheid_data',
        'form_data_bescheid_data_mapping',
        'compound_form_data',
        'group_by_el_id',

    ]; 

    protected $casts = [
        'form_data_bescheid_data_mapping' => 'array',
        'compound_form_data' => 'array',
    ];

    public function form() {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function form_containing_bescheid_data() {
        return $this->belongsTo(Form::class, 'form_id_containing_bescheid_data');
    }

}
