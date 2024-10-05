<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'age',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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
}
