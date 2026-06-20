<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max' => 255],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:active,paused,completed,archived'],
            'target_date' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max' => 7],
            'order_index' => ['nullable', 'integer'],
        ];
    }
}
