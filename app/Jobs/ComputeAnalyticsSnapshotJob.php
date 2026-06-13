<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class ComputeAnalyticsSnapshotJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly User $user,
        public readonly ?string $date = null,
    ) {}

    public function handle(AnalyticsService $analyticsService): void
    {
        $date = $this->date ? Carbon::parse($this->date) : Carbon::yesterday();
        $analyticsService->snapshotForDate($this->user, $date);
    }
}
