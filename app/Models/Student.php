<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'student_id',
        'name',
        'course',
        'year_level',
        'qr_code'
    ];

    public function scans()
    {
        return $this->hasMany(StudentScan::class, 'student_id', 'student_id');
    }
} 