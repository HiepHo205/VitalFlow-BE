<?php

namespace App\Http\Requests\Api\V1;

use App\Models\RoutineItem;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineItemRequest extends FormRequest
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
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'category' => [
                'nullable',
                'string',
                'max:255',
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],

            'duration_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440',
            ],

            'priority' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],

            'recurrence_type' => [
                'nullable',
                'string',
                'max:64',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $routineId =
                $this->route('routine')->id;

            $startTime = strtotime(
                $this->start_time
            );

            $endTime = strtotime(
                $this->end_time
            );

            $items = RoutineItem::query()
                ->where(
                    'routine_id',
                    $routineId
                )
                ->get();

            foreach ($items as $item) {

                $itemStart = strtotime(
                    $item->start_time
                );

                $itemEnd = strtotime(
                    $item->end_time
                );

                $isOverlap =
                    $startTime < $itemEnd
                    &&
                    $endTime > $itemStart;

                if ($isOverlap) {

                    $validator
                        ->errors()
                        ->add(
                            'start_time',
                            'This time overlaps with another routine item.'
                        );

                    break;
                }
            }
        });
    }
}