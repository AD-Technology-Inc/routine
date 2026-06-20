<?php

use App\Http\Controllers\Api\AIPlannerController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Goals
    Route::apiResource('goals', GoalController::class);

    // Tasks under Goals
    Route::get('goals/{goal}/tasks', [TaskController::class, 'index'])->name('goals.tasks.index');
    Route::post('goals/{goal}/tasks', [TaskController::class, 'store'])->name('goals.tasks.store');

    // Standalone Tasks
    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('tasks/{task}/skip', [TaskController::class, 'skip'])->name('tasks.skip');

    // Schedule
    Route::post('goals/{goal}/schedule/generate', [ScheduleController::class, 'generate'])->name('goals.schedule.generate');
    Route::get('goals/{goal}/schedule', [ScheduleController::class, 'show'])->name('goals.schedule.show');
    Route::get('schedule/today', [ScheduleController::class, 'today'])->name('schedule.today');
    Route::get('schedule/window', [ScheduleController::class, 'window'])->name('schedule.window');

    // Routines
    Route::get('routines', [RoutineController::class, 'index'])->name('routines.index');
    Route::post('routines', [RoutineController::class, 'store'])->name('routines.store');
    Route::put('routines/{routine}', [RoutineController::class, 'update'])->name('routines.update');
    Route::get('routines/today', [RoutineController::class, 'today'])->name('routines.today');
    Route::post('routine-instances/{instance}/steps/{step}/complete', [RoutineController::class, 'completeStep'])->name('routine-instances.steps.complete');
    Route::post('routine-instances/{instance}/skip', [RoutineController::class, 'skipInstance'])->name('routine-instances.skip');

    // AI Planner
    Route::post('goals/{goal}/ai-plan', [AIPlannerController::class, 'planGoal'])->name('goals.ai-plan');
    Route::post('goals/{goal}/ai-review', [AIPlannerController::class, 'reviewWeek'])->name('goals.ai-review');
    Route::get('ai-review', [AIPlannerController::class, 'getWeeklyReview'])->name('ai-review.show');
    Route::post('chat', [AIPlannerController::class, 'chat'])->name('ai-chat');

    // Analytics
    Route::get('analytics/summary', [AnalyticsController::class, 'summary'])->name('analytics.summary');
    Route::get('analytics/heatmap', [AnalyticsController::class, 'heatmap'])->name('analytics.heatmap');
    Route::get('analytics/energy', [AnalyticsController::class, 'energyPerformance'])->name('analytics.energy');
});
