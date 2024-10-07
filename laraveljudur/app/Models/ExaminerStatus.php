<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExaminerStatus extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * The examiners that belong to the status.
     */
    public function examiners()
    {
        return $this->hasMany(Examiner::class);
    }
}
