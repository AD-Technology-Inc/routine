<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $goal_id
 * @property string $title
 * @property string $frequency  daily|weekdays|weekends|weekly|custom
 * @property array<int, int>|null $custom_days  weekday ints 0-6
 * @property string $time_block  morning|afternoon|evening|anytime
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Goal|null $goal
 * @property-read Collection<int, RoutineStep> $steps
 * @property-read Collection<int, RoutineInstance> $instances
 */
#[Fillable(['user_id', 'goal_id', 'title', 'frequency', 'custom_days', 'time_block', 'is_active'])]
class Routine extends Model
{
    /** @use HasFactory<\Database\Factories\RoutineFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'custom_days' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RoutineStep::class)->orderBy('order_index');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(RoutineInstance::class);
    }

    /** @param Builder<Routine> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Determine if this routine should run on the given date.
     */
    public function shouldRunOnDate(Carbon $date): bool
    {
        return match ($this->frequency) {
            'daily' => true,
            'weekdays' => $date->isWeekday(),
            'weekends' => $date->isWeekend(),
            'weekly' => $date->dayOfWeek === Carbon::MONDAY,
            'custom' => in_array($date->dayOfWeek, $this->custom_days ?? [], strict: true),
            default => false,
        };
    }
}
