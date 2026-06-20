<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutineInstanceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'routine_id' => $this->routine_id,
            'user_id' => $this->user_id,
            'date' => $this->date?->toDateString(),
            'status' => $this->status,
            'completed_steps' => $this->completed_steps ?? [],
            'routine' => new RoutineResource($this->whenLoaded('routine')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
