<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examiner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'education',
        'reason',
        'examiner_status'

    ];

    /**
     * Get the volunteer that this examiner belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function examinerStatus()
    {
        return $this->belongsTo(ExaminerStatus::class, 'examiner_status');
    }
}
