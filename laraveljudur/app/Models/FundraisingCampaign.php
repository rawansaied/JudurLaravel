<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundraisingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'title',
        'description',
        'target_amount',
        'raised_amount',
        'end_date',
        'is_active',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function donations()
    {
        return $this->hasMany(Financial::class, 'campaign_id');
    }
}
