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
 * @property int $routine_id
 * @property int $user_id
 * @property Carbon $date
 * @property string $status  pending|completed|partial|skipped
 * @property array<int, int>|null $completed_step_ids
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Routine $routine
 * @property-read User $user
 */
#[Fillable(['routine_id', 'user_id', 'date', 'status', 'completed_step_ids'])]
class RoutineInstance extends Model
{
    /** @use HasFactory<\Database\Factories\RoutineInstanceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'completed_step_ids' => 'array',
        ];
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @param Builder<RoutineInstance> $query */
    public function scopeForDate(Builder $query, Carbon $date): void
    {
        $query->whereDate('date', $date);
    }

    public function isStepCompleted(int $stepId): bool
    {
        return in_array($stepId, $this->completed_step_ids ?? [], strict: true);
    }
}
