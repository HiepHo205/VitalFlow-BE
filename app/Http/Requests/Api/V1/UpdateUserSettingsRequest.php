<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserSettingsRequest extends FormRequest
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
            'theme' => ['sometimes', 'string', 'max:64'],
            'timezone' => ['sometimes', 'string', 'max:64'],
            'notification_enabled' => ['sometimes', 'boolean'],
            'ai_auto_analysis' => ['sometimes', 'boolean'],
        ];
    }
}
