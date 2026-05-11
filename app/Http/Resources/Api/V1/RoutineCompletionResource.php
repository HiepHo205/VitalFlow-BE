<?php

namespace App\Http\Resources\Api\V1;

use App\Models\RoutineCompletion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RoutineCompletion */
class RoutineCompletionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'routine_item_id' => $this->routine_item_id,
            'completed_date' => $this->completed_date,
            'status' => $this->status,
            'note' => $this->note,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
