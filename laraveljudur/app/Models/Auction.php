<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'start_date',
        'end_date',
        'starting_price',
        'current_highest_bid',
        'highest_bidder_id',
        'title',
        'description',
        'auction_status_id',
    ];

    public function itemDonation()
    {
        return $this->belongsTo(ItemDonation::class, 'item_id'); // Ensure this matches your database
    }

    public function highestBidder()
    {
        return $this->belongsTo(User::class, 'highest_bidder_id');
    } 
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

  


}
