<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function importStudents(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx'
        ]);

        try {
            $file = $request->file('file');
            $data = Excel::toArray([], $file)[0];

            // Skip header row
            array_shift($data);

            foreach ($data as $row) {
                Student::updateOrCreate(
                    ['student_id' => $row[0]], // Assuming first column is student_id
                    [
                        'name' => $row[1],
                        'course' => $row[2],
                        'year_level' => $row[3],
                    ]
                );
            }

            return response()->json(['success' => true, 'message' => 'Students imported successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function exportStudents()
    {
        $students = Student::all();
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students.csv"',
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student ID', 'Name', 'Course', 'Year Level']);

            foreach ($students as $student) {
                fputcsv($file, [
                    $student->student_id,
                    $student->name,
                    $student->course,
                    $student->year_level,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function getAnalytics()
    {
        try {
            $today = Carbon::today();
            $weekStart = Carbon::now()->startOfWeek();

            $analytics = [
                'total_validations' => StudentScan::where('status', 'success')->count(),
                'today_validations' => StudentScan::where('status', 'success')
                    ->whereDate('created_at', $today)
                    ->count(),
                'success_rate' => $this->calculateSuccessRate(),
                'weekly_trend' => $this->getWeeklyTrend(),
                'hourly_activity' => $this->getHourlyActivity(),
                'total_students' => Student::count(),
                'recent_scans' => StudentScan::with('student')
                    ->latest()
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'report_type' => 'required|string'
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        $query = StudentScan::whereBetween('created_at', [$start, $end]);

        switch ($request->report_type) {
            case 'validation_summary':
                $data = $this->generateValidationSummary($query);
                break;
            case 'detailed_activity':
                $data = $this->generateDetailedActivity($query);
                break;
            case 'failed_validations':
                $data = $this->generateFailedValidations($query);
                break;
            default:
                $data = $this->generateUsageStatistics($query);
        }

        return response()->json($data);
    }

    private function calculateSuccessRate()
    {
        $total = StudentScan::count();
        if ($total === 0) return 100; // If no scans, return 100%

        $successful = StudentScan::where('status', 'success')->count();
        return round(($successful / $total) * 100, 1);
    }

    private function getWeeklyTrend()
    {
        $weekStart = Carbon::now()->startOfWeek();
        $trend = [];
        $labels = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $count = StudentScan::where('status', 'success')
                ->whereDate('created_at', $date)
                ->count();
            $trend[] = $count;
            $labels[] = $date->format('D');
        }

        return [
            'labels' => $labels,
            'data' => $trend
        ];
    }

    private function getHourlyActivity()
    {
        $activity = [];
        $labels = [];
        
        for ($hour = 8; $hour <= 17; $hour++) {
            $count = StudentScan::where('status', 'success')
                ->whereTime('created_at', '>=', sprintf('%02d:00:00', $hour))
                ->whereTime('created_at', '<', sprintf('%02d:00:00', $hour + 1))
                ->count();
            $activity[] = $count;
            $labels[] = sprintf('%d:00', $hour);
        }

        return [
            'labels' => $labels,
            'data' => $activity
        ];
    }

    private function generateValidationSummary($query)
    {
        return [
            'total_scans' => $query->count(),
            'successful_scans' => $query->where('status', 'success')->count(),
            'failed_scans' => $query->where('status', 'failed')->count(),
            'unique_students' => $query->distinct('student_id')->count(),
        ];
    }

    private function generateDetailedActivity($query)
    {
        return $query->with('student')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($scan) {
                        return [
                            'timestamp' => $scan->created_at,
                            'student_id' => $scan->student->student_id,
                            'student_name' => $scan->student->name,
                            'status' => $scan->status,
                        ];
                    });
    }

    private function generateFailedValidations($query)
    {
        return $query->where('status', 'failed')
                    ->with('student')
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    private function generateUsageStatistics($query)
    {
        return [
            'hourly_distribution' => $this->getHourlyActivity(),
            'daily_average' => $query->count() / max(1, $query->distinct('date(created_at)')->count()),
            'peak_hours' => $this->getPeakHours($query),
        ];
    }

    private function getPeakHours($query)
    {
        $hours = [];
        for ($i = 8; $i <= 17; $i++) {
            $count = $query->whereTime('created_at', '>=', sprintf('%02d:00:00', $i))
                          ->whereTime('created_at', '<', sprintf('%02d:00:00', $i + 1))
                          ->count();
            $hours[$i] = $count;
        }

        arsort($hours);
        return array_slice($hours, 0, 3, true);
    }
} 