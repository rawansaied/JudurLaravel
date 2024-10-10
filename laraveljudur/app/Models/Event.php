<?php

// app/Models/Event.php

namespace App\Models;

use App\Models\Volunteer;
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
        'event_status',
        'image',
        'location',
        'duration',
        'people_helped',       
        'goods_distributed',  
        'allocatedMoney',
        'allocatedItems',
    ];

    /**
     * Get the land associated with the event.
     */
    public function land()
    {
        return $this->belongsTo(Land::class, 'land_id');
    }

    /**
     * Get the volunteers associated with the event.
     */
    public function volunteers()
    {
        return $this->belongsToMany(Volunteer::class, 'event_volunteer');
    }
    

    /**
     * Get the user who created the event (if applicable).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function eventStatus()
    {
        return $this->belongsTo(EventStatus::class, 'event_status', 'id');
    }
}
