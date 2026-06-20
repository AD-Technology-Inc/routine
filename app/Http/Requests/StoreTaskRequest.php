<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'parent_task_id' => ['nullable', 'integer', 'exists:tasks,id'],
            'estimated_minutes' => ['required', 'integer', 'min:1'],
            'due_date' => ['nullable', 'date'],
            'order_index' => ['nullable', 'integer'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,critical'],
            'type' => ['nullable', 'string', 'in:learning,practice,execution,review,planning'],
            'flexibility' => ['nullable', 'string', 'in:fixed,flexible,optional'],
            'reschedule_policy' => ['nullable', 'string', 'in:strict,soft,skip_allowed'],
            'energy_level' => ['nullable', 'string', 'in:low,medium,high'],
            'grouping_key' => ['nullable', 'string', 'max:255'],
            'can_merge' => ['nullable', 'boolean'],
            'can_split' => ['nullable', 'boolean'],
        ];
    }
}
