<?php

namespace App\Services;

use App\Events\TaskCompleted;
use App\Events\TaskSkipped;
use App\Models\Goal;
use App\Models\Task;
use App\Models\TaskAttribute;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class TaskService
{
    /**
     * @param array{title: string, estimated_minutes?: int, order_index?: int, due_date?: string|null, parent_task_id?: int|null} $data
     * @param array{priority?: string, type?: string, flexibility?: string, reschedule_policy?: string, energy_level?: string, grouping_key?: string|null, can_merge?: bool, can_split?: bool}|null $attributes
     */
    public function createTask(Goal $goal, array $data, ?array $attributes = null): Task
    {
        $task = $goal->tasks()->create($data);

        TaskAttribute::create(array_merge(
            ['task_id' => $task->id],
            $attributes ?? []
        ));

        return $task->load('attribute');
    }

    /**
     * @param array{title?: string, estimated_minutes?: int, order_index?: int, status?: string, due_date?: string|null} $taskData
     * @param array{priority?: string, type?: string, flexibility?: string, reschedule_policy?: string, energy_level?: string, grouping_key?: string|null, can_merge?: bool, can_split?: bool}|null $attributeData
     */
    public function updateTask(Task $task, array $taskData, ?array $attributeData = null): Task
    {
        $task->update($taskData);

        if ($attributeData !== null && $task->attribute) {
            $task->attribute->update($attributeData);
        }

        return $task->fresh(['attribute']);
    }

    public function completeTask(Task $task, User $user, int $durationMinutes = 0): TaskLog
    {
        $task->update([
            'status' => 'completed',
            'actual_minutes' => $durationMinutes > 0 ? $durationMinutes : null,
        ]);

        $log = TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'action' => 'completed',
            'duration_minutes' => $durationMinutes > 0 ? $durationMinutes : null,
        ]);

        event(new TaskCompleted($task, $user, $log));

        return $log;
    }

    public function skipTask(Task $task, User $user, ?string $notes = null): TaskLog
    {
        $log = TaskLog::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'action' => 'skipped',
            'notes' => $notes,
        ]);

        event(new TaskSkipped($task, $user, $log));

        return $log;
    }

    /** @return Collection<int, Task> */
    public function getTasksForGoal(Goal $goal): Collection
    {
        return $goal->tasks()
            ->with('attribute')
            ->orderBy('order_index')
            ->get();
    }
}
