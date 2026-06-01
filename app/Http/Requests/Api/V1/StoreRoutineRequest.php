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
            'goal_id' => ['required', 'uuid', 'exists:goals,id'],
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
            'replace_routine_ids' => ['nullable', 'array'],
            'replace_routine_ids.*' => ['uuid'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            $ranges = [];

            foreach ($items as $index => $item) {
                $start = isset($item['start_time']) ? trim($item['start_time']) : null;
                $end = isset($item['end_time']) ? trim($item['end_time']) : null;

                if ($start === null || $end === null || $start === '' || $end === '') {
                    continue;
                }

                $s = strtotime($start);
                $e = strtotime($end);

                if ($s === false || $e === false) {
                    $validator->errors()->add("items.$index.start_time", 'Thời gian không hợp lệ.');

                    continue;
                }

                if ($e <= $s) {
                    $validator->errors()->add("items.$index.end_time", 'Thời gian kết thúc phải lớn hơn thời gian bắt đầu.');

                    continue;
                }

                foreach ($ranges as $rIndex => [$rs, $re]) {
                    if ($s < $re && $e > $rs) {
                        $validator->errors()->add("items.$index", 'Thời gian item chồng chéo với item khác.');
                        $validator->errors()->add("items.$rIndex", 'Thời gian item chồng chéo với item khác.');
                    }
                }

                $ranges[$index] = [$s, $e];
            }
        });
    }
}
