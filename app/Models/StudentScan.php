<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScan extends Model
{
    protected $fillable = [
        'student_id',
        'qr_data',
        'scanned_at'
    ];

    protected $casts = [
        'scanned_at' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
} 