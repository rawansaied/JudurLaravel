<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Donor;
use App\Models\Volunteer;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens,Billable,  Notifiable;


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

    public function role()
{
    return $this->belongsTo(Role::class);
}



    public function isExaminer()
    {
        return $this->is_examiner;
    }
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
    public function events()
{
    return $this->belongsToMany(Event::class, 'event_volunteer', 'volunteer_id', 'event_id');
}


public function volunteerProfile()
{
    return $this->hasOne(Volunteer::class);
}
}
