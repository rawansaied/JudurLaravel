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
}
