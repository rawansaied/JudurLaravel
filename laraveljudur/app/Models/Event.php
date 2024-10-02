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
        'status',
        'duration' ,
       
        'people_helped',
        'goods_distributed',// Assuming you want to track duration for each event
    ];

    /**
     * Get the land associated with the event.
     */
    public function land()
    {
        return $this->belongsTo(Land::class);
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
}
