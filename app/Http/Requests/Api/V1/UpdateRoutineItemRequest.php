<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoutineItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'string', 'max:16'],
            'end_time' => ['nullable', 'string', 'max:16'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'recurrence_type' => ['nullable', 'string', 'max:64'],
        ];
    }
}
