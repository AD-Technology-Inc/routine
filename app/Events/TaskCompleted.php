<?php

namespace App\Events;

use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class TaskCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly Task $task,
        public readonly User $user,
        public readonly TaskLog $log,
    ) {}
}
