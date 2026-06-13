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
 * @property int $daily_available_minutes
 * @property array<int, string>|null $preferred_time_blocks
 * @property int|null $monday_minutes
 * @property int|null $tuesday_minutes
 * @property int|null $wednesday_minutes
 * @property int|null $thursday_minutes
 * @property int|null $friday_minutes
 * @property int|null $saturday_minutes
 * @property int|null $sunday_minutes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'daily_available_minutes',
    'preferred_time_blocks',
    'monday_minutes',
    'tuesday_minutes',
    'wednesday_minutes',
    'thursday_minutes',
    'friday_minutes',
    'saturday_minutes',
    'sunday_minutes',
])]
class UserCapacityProfile extends Model
{
    /** @use HasFactory<\Database\Factories\UserCapacityProfileFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'preferred_time_blocks' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get available minutes for a specific date, respecting weekday overrides.
     */
    public function minutesForDate(Carbon $date): int
    {
        $weekdayColumn = match ($date->dayOfWeek) {
            Carbon::MONDAY => 'monday_minutes',
            Carbon::TUESDAY => 'tuesday_minutes',
            Carbon::WEDNESDAY => 'wednesday_minutes',
            Carbon::THURSDAY => 'thursday_minutes',
            Carbon::FRIDAY => 'friday_minutes',
            Carbon::SATURDAY => 'saturday_minutes',
            Carbon::SUNDAY => 'sunday_minutes',
        };

        return $this->{$weekdayColumn} ?? $this->daily_available_minutes;
    }
}
