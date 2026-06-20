<?php

namespace App\Listeners;

use App\Events\ScheduleGenerated;
use Illuminate\Support\Facades\Cache;

class OnScheduleGenerated
{
    public function handle(ScheduleGenerated $event): void
    {
        // Clear cached plan for each date in the generated schedule
        $event->slots->pluck('date')->unique()->each(function ($date) use ($event) {
            $dateString = is_string($date) ? $date : $date->toDateString();
            Cache::forget("schedule:{$event->user->id}:{$dateString}");
        });
    }
}
