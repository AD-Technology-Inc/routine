<?php

use App\Events\TaskCompleted;
use App\Events\TaskSkipped;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Jobs\ComputeAnalyticsSnapshotJob;
use App\Jobs\GenerateRoutineInstancesJob;
use App\Jobs\GenerateUserScheduleJob;
use App\Listeners\OnTaskCompleted;
use App\Listeners\OnTaskSkipped;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withSchedule(function (Schedule $schedule): void {
        // Daily at 00:01: generate routine instances for all users
        $schedule->call(function (): void {
            User::whereHas('goals', fn ($q) => $q->where('status', 'active'))->each(
                fn (User $user) => GenerateRoutineInstancesJob::dispatch($user)->onQueue('scheduling')
            );
        })->dailyAt('00:01')->name('generate-routine-instances')->withoutOverlapping();

        // Daily at 00:05: regenerate 7-day schedule for all active users
        $schedule->call(function (): void {
            User::whereHas('goals', fn ($q) => $q->where('status', 'active'))->each(
                fn (User $user) => GenerateUserScheduleJob::dispatch($user)->onQueue('scheduling')
            );
        })->dailyAt('00:05')->name('generate-schedules')->withoutOverlapping();

        // Daily at 23:55: compute analytics snapshot for yesterday
        $schedule->call(function (): void {
            User::all()->each(
                fn (User $user) => ComputeAnalyticsSnapshotJob::dispatch($user)->onQueue('analytics')
            );
        })->dailyAt('23:55')->name('compute-analytics')->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
