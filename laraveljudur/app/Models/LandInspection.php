<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'land_id', 
        'date', 
        'examiner_id', 
        'hygiene', 
        'capacity', 
        'electricity_supply', 
        'general_condition', 
        'photo_path',
    ];
}
