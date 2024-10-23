<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financial extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id', 
        'amount', 
        'currency', 
        'payment_method'
    ];

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }
    public function campaign()
    {
        return $this->belongsTo(FundraisingCampaign::class, 'campaign_id');
    }
}
