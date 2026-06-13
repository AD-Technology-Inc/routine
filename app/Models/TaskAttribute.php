<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $task_id
 * @property string $priority  low|medium|high|critical
 * @property string $type  learning|practice|execution|review|planning
 * @property string $flexibility  fixed|flexible|optional
 * @property string $reschedule_policy  strict|soft|skip_allowed
 * @property string $energy_level  low|medium|high
 * @property string|null $grouping_key
 * @property bool $can_merge
 * @property bool $can_split
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Task $task
 */
#[Fillable(['task_id', 'priority', 'type', 'flexibility', 'reschedule_policy', 'energy_level', 'grouping_key', 'can_merge', 'can_split'])]
class TaskAttribute extends Model
{
    /** @use HasFactory<\Database\Factories\TaskAttributeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'can_merge' => 'boolean',
            'can_split' => 'boolean',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
