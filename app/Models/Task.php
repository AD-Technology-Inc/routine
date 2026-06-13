<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $goal_id
 * @property int|null $parent_task_id
 * @property string $title
 * @property int $estimated_minutes
 * @property int|null $actual_minutes
 * @property int $order_index
 * @property string $status
 * @property Carbon|null $due_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Goal $goal
 * @property-read Task|null $parent
 * @property-read Collection<int, Task> $subtasks
 * @property-read TaskAttribute|null $attribute
 * @property-read Collection<int, TaskDependency> $dependencies
 * @property-read Collection<int, TaskDependency> $dependents
 * @property-read Collection<int, TaskLog> $logs
 * @property-read Collection<int, ScheduledSlot> $scheduledSlots
 */
#[Fillable(['goal_id', 'parent_task_id', 'title', 'estimated_minutes', 'actual_minutes', 'order_index', 'status', 'due_date'])]
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function attribute(): HasOne
    {
        return $this->hasOne(TaskAttribute::class);
    }

    /** Tasks this task depends on */
    public function dependencies(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    /** Tasks that depend on this task */
    public function dependents(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    public function scheduledSlots(): HasMany
    {
        return $this->hasMany(ScheduledSlot::class);
    }

    /** @param Builder<Task> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /** @param Builder<Task> $query */
    public function scopeByPriority(Builder $query): void
    {
        $query->join('task_attributes', 'tasks.id', '=', 'task_attributes.task_id')
            ->orderByRaw("CASE task_attributes.priority
                WHEN 'critical' THEN 4
                WHEN 'high' THEN 3
                WHEN 'medium' THEN 2
                WHEN 'low' THEN 1
                ELSE 0 END DESC")
            ->select('tasks.*');
    }

    /** @param Builder<Task> $query */
    public function scopeForGroupingKey(Builder $query, string $key): void
    {
        $query->join('task_attributes', 'tasks.id', '=', 'task_attributes.task_id')
            ->where('task_attributes.grouping_key', $key)
            ->select('tasks.*');
    }
}
