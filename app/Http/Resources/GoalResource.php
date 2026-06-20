<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'target_date' => $this->target_date?->toDateString(),
            'color' => $this->color,
            'order_index' => $this->order_index,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'routines' => RoutineResource::collection($this->whenLoaded('routines')),
        ];
    }
}
