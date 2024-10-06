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
        'title',
        'description',
        'auction_status_id',
    ];

    public function itemDonation()
    {
        return $this->belongsTo(ItemDonation::class, 'item_id');
    }

    public function auctionStatus()
    {
        return $this->belongsTo(AuctionStatus::class, 'auction_status_id');
    }
}
