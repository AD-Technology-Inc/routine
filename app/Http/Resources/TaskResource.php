<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_id' => $this->goal_id,
            'parent_task_id' => $this->parent_task_id,
            'title' => $this->title,
            'estimated_minutes' => $this->estimated_minutes,
            'actual_minutes' => $this->actual_minutes,
            'order_index' => $this->order_index,
            'status' => $this->status,
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'priority' => $this->attribute?->priority,
            'type' => $this->attribute?->type,
            'flexibility' => $this->attribute?->flexibility,
            'reschedule_policy' => $this->attribute?->reschedule_policy,
            'energy_level' => $this->attribute?->energy_level,
            'grouping_key' => $this->attribute?->grouping_key,
            'can_merge' => (bool) $this->attribute?->can_merge,
            'can_split' => (bool) $this->attribute?->can_split,
            'attribute' => $this->attribute ? [
                'priority' => $this->attribute->priority,
                'type' => $this->attribute->type,
                'flexibility' => $this->attribute->flexibility,
                'reschedule_policy' => $this->attribute->reschedule_policy,
                'energy_level' => $this->attribute->energy_level,
                'grouping_key' => $this->attribute->grouping_key,
                'can_merge' => (bool) $this->attribute->can_merge,
                'can_split' => (bool) $this->attribute->can_split,
            ] : null,
            'dependencies' => $this->whenLoaded('dependencies', fn () => $this->dependencies->pluck('depends_on_task_id')),
        ];
    }
}
