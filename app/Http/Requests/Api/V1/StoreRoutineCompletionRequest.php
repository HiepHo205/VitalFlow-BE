<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineCompletionRequest extends FormRequest
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
            'routine_item_id' => ['required', 'uuid', 'exists:routine_items,id'],
            'completed_date' => ['required', 'date'],
            'status' => ['required', 'string', 'max:64'],
            'note' => ['nullable', 'string', 'max:5000'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
