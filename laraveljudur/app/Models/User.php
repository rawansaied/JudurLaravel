<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Donor;
use App\Models\Volunteer;



class User extends Authenticatable
{
    use HasFactory, HasApiTokens,  Notifiable;

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
        'phone',
        'profile_picture', 
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
    // Relationship to Donor
    public function donor()
    {
        return $this->hasOne(Donor::class);
    }

    // Relationship to Volunteer
    public function volunteer()
    {
        return $this->hasOne(volunteer::class);
    }
    
    public function isExaminer()
{
    return $this->is_examiner; // Assuming you have an is_examiner column in the users table
}
public function inspections()
{
    return $this->hasMany(LandInspection::class, 'examiner_id'); // Assuming 'examiner_id' in land_inspections references users
}

}
