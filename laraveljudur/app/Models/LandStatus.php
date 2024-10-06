<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandStatus extends Model
{
    use HasFactory;
    protected $table = 'land_statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'status',
    ];

    /**
     * The lands that have this status.
     */
    public function lands()
    {
        return $this->hasMany(Land::class);
    }
}
