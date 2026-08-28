<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MetaController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SlaController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public authentication
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/meta', [MetaController::class, 'meta']);

    // Authenticated API routes
    Route::middleware('auth:sanctum')->group(function () {
        // User session
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Dashboard Metrics
        Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);

        // Ticket Management
        Route::get('/tickets', [TicketController::class, 'index']);
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
        Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
        Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign']);
        Route::patch('/tickets/{ticket}/priority', [TicketController::class, 'updatePriority']);

        // Comments & Internal Notes
        Route::post('/tickets/{ticket}/comments', [CommentController::class, 'store']);

        // SLA Management & Monitoring
        Route::get('/sla/policies', [SlaController::class, 'policies']);
        Route::get('/sla/at-risk', [SlaController::class, 'atRisk']);
        Route::put('/sla/policies/{slaPolicy}', [SlaController::class, 'updatePolicy']);

        // Operational Reports & Analytics
        Route::get('/reports/workload', [ReportController::class, 'workload']);
        Route::get('/reports/trends', [ReportController::class, 'trends']);

        // Users & Directory
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/agents', [UserController::class, 'agents']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);

        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'update']);
    });
});
