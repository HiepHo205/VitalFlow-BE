<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalEntryRequest extends FormRequest
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
            'entry_date' => ['sometimes', 'date'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'string', 'max:20000'],
            'mood_tag' => ['nullable', 'string', 'max:64'],
        ];
    }
}
