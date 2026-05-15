<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoutineRequest extends FormRequest
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
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_ai_generated' => [
                'sometimes',
                'boolean',
            ],

            'items' => [
                'sometimes',
                'array',
            ],

            'items.*.id' => [
                'nullable',
                'uuid',
                'exists:routine_items,id',
            ],

            'items.*.title' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.description' => [
                'nullable',
                'string',
            ],

            'items.*.category' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.start_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'items.*.end_time' => [
                'nullable',
                'date_format:H:i',
            ],

            'items.*.duration_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'items.*.priority' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'items.*.recurrence_type' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }
}