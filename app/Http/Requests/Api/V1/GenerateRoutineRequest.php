<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateRoutineRequest extends FormRequest
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
            'focus' => ['nullable', 'string', 'max:2000'],
            'typical_work_hours' => ['nullable', 'string', 'max:255'],
            'activities' => ['nullable', 'array'],
            'activities.*' => ['string', 'max:255'],
            'constraints' => ['nullable', 'string', 'max:4000'],
            'replace' => ['nullable', 'boolean'],
            'goal_id' => [
                'required',
                'uuid',
                Rule::exists('goals', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
        ];
    }
}
