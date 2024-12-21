<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        DB::beginTransaction();
        try {
            $request->validate([
                'qr_data' => 'required|string'
            ]);

            // Get the QR code data
            $qr_data = $request->qr_data;
            
            // Find student in database by matching the exact QR code
            $student = Student::where('qr_code', $qr_data)
                            ->orWhere('student_id', str_replace('-VALID', '', $qr_data))
                            ->first();

            if (!$student) {
                DB::rollBack();
                Log::info('Student not found for QR code: ' . $qr_data);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code or student not found'
                ], 404);
            }

            // Check for recent scans to prevent duplicates
            $recentScan = StudentScan::where('student_id', $student->student_id)
                ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                ->where('status', 'success')
                ->first();

            if ($recentScan) {
                DB::rollBack();
                return response()->json([
                    'success' => true,
                    'message' => 'Student was recently validated (within 5 minutes)',
                    'data' => [
                        'student' => $student,
                        'scan' => $recentScan,
                        'semester' => 'First',
                        'academic_year' => '2023-2024'
                    ]
                ]);
            }

            // Create scan record
            $scan = new StudentScan();
            $scan->student_id = $student->student_id;
            $scan->qr_data = $qr_data;
            $scan->status = 'success';
            $scan->message = 'Student validated successfully';
            $scan->save();

            Log::info('Student scan successful', [
                'student_id' => $student->student_id,
                'qr_data' => $qr_data
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student validated successfully',
                'data' => [
                    'student' => $student,
                    'scan' => $scan,
                    'semester' => 'First',
                    'academic_year' => '2023-2024'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QR Scan Error: ' . $e->getMessage(), [
                'qr_data' => $request->qr_data ?? 'not provided',
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing scan. Please try again.'
            ], 500);
        }
    }

    /**
     * Get validated students with their latest scan
     */
    public function getValidated()
    {
        $validatedScans = \App\Models\StudentScan::with('student')
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'student' => [
                        'student_id' => $scan->student->student_id,
                        'name' => $scan->student->name,
                        'course' => $scan->student->course,
                        'year_level' => $scan->student->year_level,
                    ],
                    'status' => $scan->status,
                    'message' => $scan->message,
                    'created_at' => $scan->created_at,
                ];
            });

        return response()->json($validatedScans);
    }

    /**
     * Get scan history with student details
     */
    public function getHistory()
    {
        $scans = \App\Models\StudentScan::with('student')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($scan) {
                return [
                    'id' => $scan->id,
                    'student' => [
                        'student_id' => $scan->student->student_id,
                        'name' => $scan->student->name,
                        'course' => $scan->student->course,
                        'year_level' => $scan->student->year_level,
                    ],
                    'status' => $scan->status,
                    'message' => $scan->message,
                    'created_at' => $scan->created_at,
                ];
            });

        return response()->json($scans);
    }

    public function show(StudentScan $scan)
    {
        return response()->json($scan);
    }
} 