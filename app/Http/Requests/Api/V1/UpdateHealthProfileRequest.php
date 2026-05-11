<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHealthProfileRequest extends FormRequest
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
            'age' => ['sometimes', 'integer', 'min:1', 'max:130'],
            'gender' => ['nullable', 'string', 'max:64'],
            'height_cm' => ['sometimes', 'numeric', 'min:50', 'max:280'],
            'weight_kg' => ['sometimes', 'numeric', 'min:20', 'max:400'],
            'work_type' => ['nullable', 'string', 'max:255'],
            'baseline_sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'baseline_stress_level' => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }
}
