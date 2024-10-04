<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'user', 'text', 'date']; // Define the fillable fields

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
