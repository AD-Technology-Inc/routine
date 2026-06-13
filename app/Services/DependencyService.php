<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DependencyService
{
    public function addDependency(Task $task, Task $dependsOn): TaskDependency
    {
        if ($task->id === $dependsOn->id) {
            throw new \InvalidArgumentException('A task cannot depend on itself.');
        }

        if ($this->hasCyclicDependency($task, $dependsOn)) {
            throw new \InvalidArgumentException("Adding this dependency would create a cycle.");
        }

        return TaskDependency::firstOrCreate([
            'task_id' => $task->id,
            'depends_on_task_id' => $dependsOn->id,
        ]);
    }

    public function removeDependency(Task $task, Task $dependsOn): bool
    {
        return (bool) TaskDependency::where('task_id', $task->id)
            ->where('depends_on_task_id', $dependsOn->id)
            ->delete();
    }

    /**
     * Return tasks ordered by dependency resolution using Kahn's topological sort.
     * Tasks with no unresolved dependencies come first.
     *
     * @param Collection<int, Task> $tasks
     * @return Collection<int, Task>
     */
    public function getUnblockedTasks(Collection $tasks): Collection
    {
        $taskIds = $tasks->pluck('id')->all();

        // Load all dependency edges within the task set
        $edges = TaskDependency::whereIn('task_id', $taskIds)
            ->whereIn('depends_on_task_id', $taskIds)
            ->get(['task_id', 'depends_on_task_id']);

        // Build in-degree map and adjacency list
        $inDegree = array_fill_keys($taskIds, 0);
        $adjacency = array_fill_keys($taskIds, []);

        foreach ($edges as $edge) {
            $inDegree[$edge->task_id]++;
            $adjacency[$edge->depends_on_task_id][] = $edge->task_id;
        }

        // Kahn's algorithm
        $queue = array_keys(array_filter($inDegree, fn (int $deg): bool => $deg === 0));
        $sorted = [];

        while (! empty($queue)) {
            $current = array_shift($queue);
            $sorted[] = $current;

            foreach ($adjacency[$current] ?? [] as $dependent) {
                $inDegree[$dependent]--;
                if ($inDegree[$dependent] === 0) {
                    $queue[] = $dependent;
                }
            }
        }

        // Tasks not in sorted are in a cycle — we still include them at the end
        $remaining = array_diff($taskIds, $sorted);

        $orderedIds = array_merge($sorted, $remaining);
        $indexed = $tasks->keyBy('id');

        return collect($orderedIds)
            ->filter(fn (int $id): bool => $indexed->has($id))
            ->map(fn (int $id): Task => $indexed->get($id))
            ->values();
    }

    /**
     * Detect if adding $newDep as a dependency of $task would create a cycle.
     * Uses DFS from $newDep to see if it can reach $task.
     */
    public function hasCyclicDependency(Task $task, Task $newDep): bool
    {
        $visited = [];
        $stack = [$newDep->id];

        while (! empty($stack)) {
            $current = array_pop($stack);

            if ($current === $task->id) {
                return true;
            }

            if (in_array($current, $visited, strict: true)) {
                continue;
            }

            $visited[] = $current;

            $dependsOnIds = TaskDependency::where('task_id', $current)
                ->pluck('depends_on_task_id')
                ->all();

            foreach ($dependsOnIds as $id) {
                $stack[] = $id;
            }
        }

        return false;
    }
}
