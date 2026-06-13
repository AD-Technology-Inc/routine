<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $task_id
 * @property int $user_id
 * @property Carbon $date
 * @property string $action  scheduled|completed|skipped|missed|rescheduled|split|merged
 * @property string|null $notes
 * @property int|null $duration_minutes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Task $task
 * @property-read User $user
 */
#[Fillable(['task_id', 'user_id', 'date', 'action', 'notes', 'duration_minutes'])]
class TaskLog extends Model
{
    /** @use HasFactory<\Database\Factories\TaskLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param Builder<TaskLog> $query */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('action', 'completed');
    }

    /** @param Builder<TaskLog> $query */
    public function scopeMissed(Builder $query): void
    {
        $query->where('action', 'missed');
    }

    /** @param Builder<TaskLog> $query */
    public function scopeForDate(Builder $query, Carbon $date): void
    {
        $query->whereDate('date', $date);
    }
}
