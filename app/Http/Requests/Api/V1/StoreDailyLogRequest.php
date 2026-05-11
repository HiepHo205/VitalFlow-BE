<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDailyLogRequest extends FormRequest
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
            'log_date' => [
                'required',
                'date',
                Rule::unique('daily_logs', 'log_date')->where('user_id', $this->user()->id),
            ],
            'mood_score' => ['nullable', 'integer', 'min:0', 'max:10'],
            'energy_level' => ['nullable', 'integer', 'min:0', 'max:10'],
            'stress_level' => ['nullable', 'integer', 'min:0', 'max:10'],
            'sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'water_intake_ml' => ['nullable', 'integer', 'min:0'],
            'body_condition' => ['nullable', 'string', 'max:5000'],
            'productivity_score' => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }
}
