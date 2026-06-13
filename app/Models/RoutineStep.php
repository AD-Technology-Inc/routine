<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $routine_id
 * @property string $title
 * @property int $estimated_minutes
 * @property string $energy_level  low|medium|high
 * @property int $order_index
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Routine $routine
 */
#[Fillable(['routine_id', 'title', 'estimated_minutes', 'energy_level', 'order_index'])]
class RoutineStep extends Model
{
    /** @use HasFactory<\Database\Factories\RoutineStepFactory> */
    use HasFactory;

    public function routine(): BelongsTo
    {
        return $this->belongsTo(Routine::class);
    }
}
