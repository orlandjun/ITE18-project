<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentScan;
use Illuminate\Http\Request;

class StudentScanController extends Controller
{
    public function index()
    {
        $scans = StudentScan::with('student')
            ->latest()
            ->take(50)
            ->get();

        return response()->json($scans);
    }

    public function store(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        // Try to find student by QR code
        $student = Student::where('qr_code', $request->qr_data)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code or student not found',
            ], 404);
        }

        $scan = StudentScan::create([
            'student_id' => $student->student_id,
            'qr_data' => $request->qr_data,
            'scanned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student validated successfully',
            'data' => [
                'scan' => $scan,
                'student' => [
                    'name' => $student->name,
                    'student_id' => $student->student_id,
                    'course' => $student->course,
                    'year_level' => $student->year_level
                ]
            ]
        ]);
    }

    public function show(StudentScan $scan)
    {
        return response()->json($scan);
    }
} 