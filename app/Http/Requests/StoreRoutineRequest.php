<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'goal_id' => ['nullable', 'integer', 'exists:goals,id'],
            'title' => ['required', 'string', 'max:255'],
            'frequency' => ['required', 'string', 'in:daily,weekdays,weekends,weekly,custom'],
            'custom_days' => ['nullable', 'array'],
            'custom_days.*' => ['integer', 'min:0', 'max:6'],
            'time_block' => ['required', 'string', 'in:morning,afternoon,evening,anytime'],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.title' => ['required', 'string', 'max:255'],
            'steps.*.estimated_minutes' => ['required', 'integer', 'min:1'],
            'steps.*.energy_level' => ['required', 'string', 'in:low,medium,high'],
            'steps.*.order_index' => ['required', 'integer'],
        ];
    }
}
