<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutineResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_id' => $this->goal_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'frequency' => $this->frequency,
            'custom_days' => $this->custom_days,
            'time_block' => $this->time_block,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'steps' => $this->whenLoaded('steps', function () {
                return $this->steps->map(fn ($step) => [
                    'id' => $step->id,
                    'routine_id' => $step->routine_id,
                    'title' => $step->title,
                    'estimated_minutes' => $step->estimated_minutes,
                    'energy_level' => $step->energy_level,
                    'order_index' => $step->order_index,
                ]);
            }),
        ];
    }
}
