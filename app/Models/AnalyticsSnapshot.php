<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property Carbon $date
 * @property int $total_tasks_scheduled
 * @property int $total_tasks_completed
 * @property int $total_tasks_skipped
 * @property int $total_tasks_missed
 * @property float $completion_rate
 * @property float|null $avg_task_duration_minutes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'date',
    'total_tasks_scheduled',
    'total_tasks_completed',
    'total_tasks_skipped',
    'total_tasks_missed',
    'completion_rate',
    'avg_task_duration_minutes',
])]
class AnalyticsSnapshot extends Model
{
    /** @use HasFactory<\Database\Factories\AnalyticsSnapshotFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'completion_rate' => 'float',
            'avg_task_duration_minutes' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
