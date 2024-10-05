<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Donor;
use App\Models\Volunteer;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'age',
        'phone',
        'profile_picture', 
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function inspections()
    {
        return $this->hasMany(LandInspection::class, 'examiner_id');
    }

    public function donors()
    {
        return $this->hasMany(Donor::class);
    }

    public function volunteers()
    {
        return $this->hasMany(Volunteer::class);
    }

    public function examiners()
    {
        return $this->hasMany(Examiner::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function examiner()
    {
        return $this->hasOne(Examiner::class);
    }

    public function volunteer()
    {
        return $this->hasOne(Volunteer::class);
    }

    public function landInspections()
    {
        return $this->hasMany(LandInspection::class, 'examiner_id');
    }
}
