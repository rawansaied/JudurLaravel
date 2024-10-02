<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Land extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_id', 
        'description', 
        'land_size', 
        'address', 
        'proof_of_ownership', 
        'status_id'
    ];
    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }
}
