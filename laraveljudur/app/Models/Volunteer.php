<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volunteer extends Model
{
    use HasFactory;

    protected $table = 'volunteers';

    protected $fillable = [
        'user_id',
        'is_examiner', 
        'skills',
        'availability',
        'volunteer_status',
        'aim',
    ];

    /**
     * Get the user that owns the volunteer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the volunteer status associated with the volunteer.
     */
    public function status()
    {
        return $this->belongsTo(VolunteerStatus::class, 'volunteer_status');
    }

    /**
     * Get the events that the volunteer is associated with.
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_volunteer');
    }
    public function lands()
    {
        return $this->hasMany(Land::class);
    }

    public function volunteerStatus()
    {
        return $this->belongsTo(VolunteerStatus::class, 'volunteer_status', 'id');
    }
}
