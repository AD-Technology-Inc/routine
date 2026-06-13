<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SchedulingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class GenerateUserScheduleJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 5;

    public function __construct(
        public readonly User $user,
        public readonly int $days = 7,
        public readonly ?string $startDate = null,
    ) {}

    public function handle(SchedulingService $scheduler): void
    {
        $start = $this->startDate ? Carbon::parse($this->startDate) : Carbon::today();
        $scheduler->generateSchedule($this->user, $start, $this->days);
    }
}
