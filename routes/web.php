<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::get('goals/{goal}', function (\App\Models\Goal $goal) {
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }
        return inertia('goals/Show', [
            'goalId' => $goal->id,
        ]);
    })->name('goals.show');
    Route::inertia('tasks', 'tasks/Board')->name('tasks.board');
    Route::inertia('routines', 'routines/Index')->name('routines.index');
    Route::inertia('analytics', 'analytics/Index')->name('analytics.index');
    Route::inertia('ai-coach', 'ai/Chat')->name('ai.chat');
});

require __DIR__.'/settings.php';
