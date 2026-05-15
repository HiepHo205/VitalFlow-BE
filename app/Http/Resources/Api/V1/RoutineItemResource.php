<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RoutineItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoutineItem */
class RoutineItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(
        Request $request
    ): array {
        return [
            'id' => $this->id,

            'routine_id' =>
                $this->routine_id,

            'title' =>
                $this->title,

            'description' =>
                $this->description,

            'category' =>
                $this->category,

            'start_time' =>
                $this->start_time,

            'end_time' =>
                $this->end_time,

            'duration_minutes' =>
                $this->duration_minutes,

            'priority' =>
                $this->priority,

            'recurrence_type' =>
                $this->recurrence_type,

            'completions' =>
                RoutineCompletionResource::collection(
                    $this->whenLoaded(
                        'completions'
                    )
                ),

            'created_at' =>
                $this->created_at,

            'updated_at' =>
                $this->updated_at,
        ];
    }
}