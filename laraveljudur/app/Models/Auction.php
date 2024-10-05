<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    use HasFactory;

    // الحقول التي يمكن تعبئتها
    protected $fillable = [
        'item_id',
        'start_date',
        'end_date',
        'starting_price',
        'title',
        'description',
    ];

    // العلاقة مع جدول item_donations
    public function itemDonation()
    {
        return $this->belongsTo(ItemDonation::class, 'item_id');
    }
}
