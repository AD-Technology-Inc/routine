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
 * @property int $user_id
 * @property int|null $task_id
 * @property string|null $grouping_key
 * @property Carbon $date
 * @property string $time_block  morning|afternoon|evening|anytime
 * @property int $allocated_minutes
 * @property int $slot_index
 * @property bool $is_merged
 * @property array<int, int>|null $merged_task_ids
 * @property string $status  pending|completed|skipped
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Task|null $task
 */
#[Fillable([
    'user_id',
    'task_id',
    'grouping_key',
    'date',
    'time_block',
    'allocated_minutes',
    'slot_index',
    'is_merged',
    'merged_task_ids',
    'status',
])]
class ScheduledSlot extends Model
{
    /** @use HasFactory<\Database\Factories\ScheduledSlotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_merged' => 'boolean',
            'merged_task_ids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /** @param Builder<ScheduledSlot> $query */
    public function scopeForDate(Builder $query, Carbon $date): void
    {
        $query->whereDate('date', $date);
    }

    /** @param Builder<ScheduledSlot> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }
}
