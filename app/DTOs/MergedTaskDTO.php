<?php

namespace App\DTOs;

use App\Models\Task;
use Illuminate\Support\Collection;

class MergedTaskDTO
{
    /**
     * @param Collection<int, Task> $tasks
     * @param array<int, int> $taskIds
     */
    public function __construct(
        public readonly Collection $tasks,
        public readonly array $taskIds,
        public readonly int $totalMinutes,
        public readonly string $groupingKey,
    ) {}
}
