<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentScanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Student Scan Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/scan', [StudentScanController::class, 'store']);
    Route::get('/scans', [StudentScanController::class, 'index']); // To get scan history
    Route::get('/scans/{scan}', [StudentScanController::class, 'show']); // To get specific scan details
}); 