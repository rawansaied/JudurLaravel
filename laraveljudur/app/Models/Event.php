<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'land_id', 
        'description', 
        'date', 
        'time',
        'expected_organizer_number', 
        'status'
    ];
}
