<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'donor_id_number',
    ];

    // Relationship to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship to Financial Donations
    public function financialDonations()
    {
        return $this->hasMany(Financial::class, 'donor_id');
    }

    // Relationship to Item Donations
    public function itemDonations()
    {
        return $this->hasMany(ItemDonation::class, 'donor_id');
    }

    // Relationship to Land Donations
    public function landDonations()
    {
        return $this->hasMany(Land::class, 'donor_id');
    }


    // Relationship to Financial (Donations)
    public function donations()
    {
        return $this->hasMany(Financial::class, 'donor_id');
    }

    // Method to get the last donation
    public function lastDonation()
    {
        return $this->hasOne(Financial::class, 'donor_id')->latest();
    }

    // Method to get the total donations amount
    public function totalDonations()
    {
        return $this->hasMany(Financial::class, 'donor_id')->sum('amount');
    }

}