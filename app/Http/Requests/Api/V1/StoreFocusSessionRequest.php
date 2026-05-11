<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreFocusSessionRequest extends FormRequest
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
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'task_name' => ['nullable', 'string', 'max:255'],
            'distraction_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
