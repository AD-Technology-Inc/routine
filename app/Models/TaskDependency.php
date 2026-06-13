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
 * @property int $depends_on_task_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Task $task
 * @property-read Task $dependsOnTask
 */
#[Fillable(['task_id', 'depends_on_task_id'])]
class TaskDependency extends Model
{
    /** @use HasFactory<\Database\Factories\TaskDependencyFactory> */
    use HasFactory;

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function dependsOnTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'depends_on_task_id');
    }
}
