<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Jobs\GenerateUserScheduleJob;
use Illuminate\Support\Facades\Cache;

class OnTaskCompleted
{
    public function handle(TaskCompleted $event): void
    {
        // Invalidate today's cached schedule
        Cache::forget("schedule:{$event->user->id}:" . now()->toDateString());

        // Re-queue schedule generation so rolling window stays fresh
        GenerateUserScheduleJob::dispatch($event->user)->onQueue('scheduling');
    }
}
