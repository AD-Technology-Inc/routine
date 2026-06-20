<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduledSlotResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'task_id' => $this->task_id,
            'grouping_key' => $this->grouping_key,
            'date' => $this->date?->toDateString(),
            'time_block' => $this->time_block,
            'allocated_minutes' => $this->allocated_minutes,
            'slot_index' => $this->slot_index,
            'is_merged' => (bool) $this->is_merged,
            'merged_task_ids' => $this->merged_task_ids,
            'status' => $this->status,
            'task' => new TaskResource($this->whenLoaded('task')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
