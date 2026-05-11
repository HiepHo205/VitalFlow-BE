<?php

namespace App\Http\Resources\Api\V1;

use App\Models\AiFeedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AiFeedback */
class AiFeedbackResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'feedback_type' => $this->feedback_type,
            'summary' => $this->summary,
            'recommendation' => $this->recommendation,
            'related_log_id' => $this->related_log_id,
            'related_routine_id' => $this->related_routine_id,
            'generated_at' => $this->generated_at,
            'created_at' => $this->created_at,
        ];
    }
}
