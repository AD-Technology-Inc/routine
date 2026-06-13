<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\RoutineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class GenerateRoutineInstancesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly User $user,
        public readonly ?string $date = null,
    ) {}

    public function handle(RoutineService $routineService): void
    {
        $date = $this->date ? Carbon::parse($this->date) : Carbon::today();
        $routineService->generateInstancesForDate($this->user, $date);
    }
}
