<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'health_profile' => ['nullable', 'array'],
            'health_profile.age' => ['required_with:health_profile', 'integer', 'min:1', 'max:130'],
            'health_profile.gender' => ['nullable', 'string', 'max:64'],
            'health_profile.height_cm' => ['required_with:health_profile', 'numeric', 'min:50', 'max:280'],
            'health_profile.weight_kg' => ['required_with:health_profile', 'numeric', 'min:20', 'max:400'],
            'health_profile.work_type' => ['nullable', 'string', 'max:255'],
            'health_profile.baseline_sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'health_profile.baseline_stress_level' => ['nullable', 'integer', 'min:0', 'max:10'],
        ];
    }
}
