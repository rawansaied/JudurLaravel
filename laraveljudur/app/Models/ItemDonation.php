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
        'status_id'
    ];





    public function auctions()
    {
        return $this->hasMany(Auction::class, 'item_id');
    }








    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }
}
