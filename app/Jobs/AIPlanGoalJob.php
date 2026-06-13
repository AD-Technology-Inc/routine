<?php

namespace App\Jobs;

use App\Models\Goal;
use App\Models\User;
use App\Services\AIPlannerService;
use App\Services\TaskService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AIPlanGoalJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 60;

    public function __construct(
        public readonly Goal $goal,
        public readonly User $user,
    ) {}

    public function handle(AIPlannerService $aiPlanner, TaskService $taskService): void
    {
        $suggestions = $aiPlanner->generateRoadmap($this->goal, $this->user);

        foreach ($suggestions as $index => $suggestion) {
            $taskService->createTask(
                $this->goal,
                [
                    'title' => $suggestion['title'],
                    'estimated_minutes' => $suggestion['estimated_minutes'],
                    'order_index' => $index,
                ],
                [
                    'type' => $suggestion['type'],
                    'priority' => $suggestion['priority'],
                ]
            );
        }
    }
}
