<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDonation extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id', 
        'item_name', 
        'value', 
        'is_valuable', 
        'condition', 
        'status_id',
        'image', ];

        public function auction()
        {
            return $this->hasOne(Auction::class, 'item_donation_id', 'id'); // Adjust the foreign key and local key as necessary
        }

    // Relationship to donor
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }
}
