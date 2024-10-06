<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionStatus extends Model
{
    protected $table = 'auction_statuses';
    use HasFactory;
    protected $fillable = ['name'];

}
