<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeeklyReportRequest extends FormRequest
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
            'week_start' => ['required', 'date'],
            'week_end' => ['required', 'date', 'after_or_equal:week_start'],
            'summary' => ['required', 'string', 'max:20000'],
            'avg_mood' => ['nullable', 'numeric'],
            'avg_energy' => ['nullable', 'numeric'],
            'avg_sleep' => ['nullable', 'numeric'],
            'burnout_risk_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
