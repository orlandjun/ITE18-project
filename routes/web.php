<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentScanController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/student-scan', [StudentScanController::class, 'store'])->name('student.scan');
Route::get('/student-scan/history', [StudentScanController::class, 'getHistory'])->name('student.scan.history');
Route::get('/student-scan/validated', [StudentScanController::class, 'getValidated'])->name('student.scan.validated');

// Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/import-students', [AdminController::class, 'importStudents']);
    Route::get('/api/export-students', [AdminController::class, 'exportStudents']);
    Route::get('/api/analytics', [AdminController::class, 'getAnalytics']);
    Route::post('/api/generate-report', [AdminController::class, 'generateReport']);
});

// Temporary route to check seeded data
Route::get('/check-students', function () {
    $students = \App\Models\Student::all();
    return response()->json($students);
});

require __DIR__.'/auth.php';
