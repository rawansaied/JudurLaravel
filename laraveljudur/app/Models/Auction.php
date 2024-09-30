<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'item_id',
        'start_date',
        'end_date',
        'starting_price',
        'title',
        'description',
    ];

    /**
     * Get the item associated with the auction.
     */
    public function item()
    {
        return $this->belongsTo(ItemDonation::class, 'item_id');
    }

    /**
     * Get the bids associated with the auction.
     */
    public function bids()
    {
        return $this->hasMany(Bid::class);
    }
}
