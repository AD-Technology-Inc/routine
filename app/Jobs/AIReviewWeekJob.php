<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AIPlannerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class AIReviewWeekJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        public readonly User $user,
    ) {}

    public function handle(AIPlannerService $aiPlanner): void
    {
        $review = $aiPlanner->generateWeeklyReview($this->user);

        Cache::put("weekly_review:{$this->user->id}", $review, now()->addDays(7));
    }
}
