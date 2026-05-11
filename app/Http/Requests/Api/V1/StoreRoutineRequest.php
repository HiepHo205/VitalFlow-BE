<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_ai_generated' => ['sometimes', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required_with:items', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.category' => ['nullable', 'string', 'max:255'],
            'items.*.start_time' => ['nullable', 'string', 'max:16'],
            'items.*.end_time' => ['nullable', 'string', 'max:16'],
            'items.*.duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'items.*.priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'items.*.recurrence_type' => ['nullable', 'string', 'max:64'],
        ];
    }
}
