<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'land_id', 'description', 'date', 'time', 
        'expected_organizer_number', 'status', 'image', 'location', 'duration',
    ];

    public function land()
    {
        return $this->belongsTo(Land::class);
    }
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

}
