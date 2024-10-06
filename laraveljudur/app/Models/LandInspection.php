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

    public function land()
    {
        return $this->belongsTo(Land::class,'land_id');
    }
    public function volunteer()
    {
        return $this->belongsTo(User::class, 'volunteer_id');
    }

    public function examiner()
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }
}
