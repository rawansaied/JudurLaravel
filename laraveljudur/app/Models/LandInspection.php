<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandInspection extends Model
{
    use HasFactory;
    protected $fillable = ['land_id', 'examiner_id', 'date', 'hygiene', 'capacity', 'electricity_supply', 'general_condition', 'photo_path', 'summary','suggestions'];

    // Each land inspection is associated with an examiner (a user)
    public function examiner()
    {
        return $this->belongsTo(User::class, 'examiner_id');
    }

    // Each land inspection belongs to a land
    public function land()
    {
        return $this->belongsTo(Land::class);
    }
    public function inspections()
    {
        return $this->belongsTo(LandInspection::class); // Adjust to the appropriate model and relationship
    }
}

