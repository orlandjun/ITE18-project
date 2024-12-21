<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentScan extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'qr_data',
        'status',
        'message'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $with = ['student'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($scan) {
            if (empty($scan->status)) {
                $scan->status = 'success';
            }
            if (empty($scan->message)) {
                $scan->message = 'Student validated successfully';
            }
        });
    }
} 