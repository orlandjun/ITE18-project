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

    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function scans()
    {
        return $this->hasMany(StudentScan::class, 'student_id', 'student_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            if (empty($student->qr_code)) {
                $student->qr_code = $student->student_id . '-VALID';
            }
        });
    }
} 