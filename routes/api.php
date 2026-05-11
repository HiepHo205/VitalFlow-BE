<?php

use App\Http\Controllers\Api\V1\AiFeedbackController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DailyLogController;
use App\Http\Controllers\Api\V1\FocusSessionController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\HealthProfileController;
use App\Http\Controllers\Api\V1\InAppNotificationController;
use App\Http\Controllers\Api\V1\JournalEntryController;
use App\Http\Controllers\Api\V1\RoutineCompletionController;
use App\Http\Controllers\Api\V1\RoutineController;
use App\Http\Controllers\Api\V1\RoutineItemController;
use App\Http\Controllers\Api\V1\UserSettingsController;
use App\Http\Controllers\Api\V1\WeeklyReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
    });

    Route::post('auth/refresh', [AuthController::class, 'refresh'])->middleware('jwt.refresh');

    Route::middleware(['jwt.auth'])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('health-profile', [HealthProfileController::class, 'show']);
        Route::put('health-profile', [HealthProfileController::class, 'upsert']);

        Route::get('user-settings', [UserSettingsController::class, 'show']);
        Route::patch('user-settings', [UserSettingsController::class, 'update']);

        Route::post('routines/generate', [RoutineController::class, 'generate']);
        Route::post('routines/{routine}/analyze', [RoutineController::class, 'analyze']);
        Route::apiResource('routines', RoutineController::class);
        Route::apiResource('routines.items', RoutineItemController::class)->scoped()->except(['create', 'edit']);

        Route::apiResource('goals', GoalController::class);

        Route::apiResource('daily-logs', DailyLogController::class);

        Route::apiResource('journal-entries', JournalEntryController::class);

        Route::get('ai-feedbacks', [AiFeedbackController::class, 'index']);
        Route::get('ai-feedbacks/{ai_feedback}', [AiFeedbackController::class, 'show']);

        Route::apiResource('routine-completions', RoutineCompletionController::class)->except(['create', 'edit', 'update']);

        Route::apiResource('weekly-reports', WeeklyReportController::class)->except(['create', 'edit', 'update']);

        Route::apiResource('focus-sessions', FocusSessionController::class)->except(['create', 'edit', 'update']);

        Route::get('notifications', [InAppNotificationController::class, 'index']);
        Route::patch('notifications/{id}/read', [InAppNotificationController::class, 'markRead']);
    });
});
