<?php

use App\Jobs\ComputeAnalyticsSnapshotJob;
use App\Jobs\GenerateRoutineInstancesJob;
use App\Jobs\GenerateUserScheduleJob;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily at 00:01: generate routine instances for all users
Schedule::call(function (): void {
    User::whereHas('goals', fn ($q) => $q->where('status', 'active'))->each(
        fn (User $user) => GenerateRoutineInstancesJob::dispatch($user)->onQueue('scheduling')
    );
})->dailyAt('00:01')->name('generate-routine-instances')->withoutOverlapping();

// Daily at 00:05: regenerate 7-day schedule for all active users
Schedule::call(function (): void {
    User::whereHas('goals', fn ($q) => $q->where('status', 'active'))->each(
        fn (User $user) => GenerateUserScheduleJob::dispatch($user)->onQueue('scheduling')
    );
})->dailyAt('00:05')->name('generate-schedules')->withoutOverlapping();

// Daily at 23:55: compute analytics snapshot for yesterday
Schedule::call(function (): void {
    User::all()->each(
        fn (User $user) => ComputeAnalyticsSnapshotJob::dispatch($user)->onQueue('analytics')
    );
})->dailyAt('23:55')->name('compute-analytics')->withoutOverlapping();
