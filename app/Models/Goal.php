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
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $target_date
 * @property string|null $color
 * @property int $order_index
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User $user
 * @property-read Collection<int, Task> $tasks
 * @property-read Collection<int, Routine> $routines
 */
#[Fillable(['user_id', 'title', 'description', 'status', 'target_date', 'color', 'order_index'])]
class Goal extends Model
{
    /** @use HasFactory<\Database\Factories\GoalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function routines(): HasMany
    {
        return $this->hasMany(Routine::class);
    }

    /** @param Builder<Goal> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /** @param Builder<Goal> $query */
    public function scopeForUser(Builder $query, User $user): void
    {
        $query->where('user_id', $user->id);
    }
}
