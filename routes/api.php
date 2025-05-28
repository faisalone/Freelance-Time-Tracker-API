<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TimeLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // User profile route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Resource routes
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('time-logs', TimeLogController::class);

    // Time log specific actions
    Route::prefix('time-logs')->group(function () {
        Route::post('start', [TimeLogController::class, 'start'])
            ->name('time-logs.start');
        Route::post('stop/{timeLog}', [TimeLogController::class, 'stop'])
            ->name('time-logs.stop');
        Route::get('running', [TimeLogController::class, 'running'])
            ->name('time-logs.running');
    });

    // Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('summary', [ReportController::class, 'summary']);
        Route::get('client/{client}', [ReportController::class, 'client']);
        Route::get('export/pdf', [ReportController::class, 'exportPdf']);
    });
});
