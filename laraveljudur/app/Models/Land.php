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

    public function lands()
    {
        return $this->belongsToMany(Volunteer::class, 'examiner_Land');
    }
    public function status()
    {
        return $this->belongsTo(LandStatus::class, 'status_id');
    }
    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class);
    }

    public function inspections()
    {
        return $this->hasMany(LandInspection::class);
    }
    public function donor()
    {
        return $this->belongsTo(Donor::class, 'donor_id');
    }
    public function events() 
    {
        return $this->hasMany(Event::class, 'land_id'); 
    }
}
