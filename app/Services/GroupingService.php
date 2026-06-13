<?php

namespace App\Services;

use App\DTOs\MergedTaskDTO;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class GroupingService
{
    /**
     * Group tasks by their grouping_key attribute.
     * Tasks with no grouping_key are returned in a '__ungrouped__' bucket.
     *
     * @param Collection<int, Task> $tasks
     * @return array<string, Collection<int, Task>>
     */
    public function groupByKey(Collection $tasks): array
    {
        $groups = [];

        foreach ($tasks as $task) {
            $key = $task->attribute?->grouping_key ?? '__ungrouped__';
            $groups[$key] ??= collect();
            $groups[$key]->push($task);
        }

        return $groups;
    }

    /**
     * Merge a collection of tasks into a single MergedTaskDTO if they are all mergeable.
     * Returns null if the group cannot be merged.
     *
     * @param Collection<int, Task> $tasks
     */
    public function mergeTasks(Collection $tasks, string $groupingKey): ?MergedTaskDTO
    {
        $mergeable = $tasks->filter(fn (Task $t): bool => (bool) $t->attribute?->can_merge);

        if ($mergeable->isEmpty()) {
            return null;
        }

        return new MergedTaskDTO(
            tasks: $mergeable,
            taskIds: $mergeable->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            totalMinutes: (int) $mergeable->sum('estimated_minutes'),
            groupingKey: $groupingKey,
        );
    }

    public function canMerge(Task $a, Task $b): bool
    {
        $attrA = $a->attribute;
        $attrB = $b->attribute;

        if ($attrA === null || $attrB === null) {
            return false;
        }

        return $attrA->can_merge
            && $attrB->can_merge
            && $attrA->grouping_key !== null
            && $attrA->grouping_key === $attrB->grouping_key;
    }
}
