<?php

namespace App\Listeners;

use App\Events\TaskSkipped;
use App\Jobs\GenerateUserScheduleJob;
use Illuminate\Support\Facades\Cache;

class OnTaskSkipped
{
    public function handle(TaskSkipped $event): void
    {
        Cache::forget("schedule:{$event->user->id}:" . now()->toDateString());
        GenerateUserScheduleJob::dispatch($event->user)->onQueue('scheduling');
    }
}
