<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skills',
        'availability',
        'aim',
        'volunteer_status',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Volunteer Status
    public function volunteerStatus()
    {
        return $this->belongsTo(VolunteerStatus::class, 'volunteer_status', 'id');
    }
}
