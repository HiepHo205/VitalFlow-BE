<?php

namespace App\Http\Resources\Api\V1;

use App\Models\HealthProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HealthProfile */
class HealthProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'age' => $this->age,
            'gender' => $this->gender,
            'height_cm' => $this->height_cm,
            'weight_kg' => $this->weight_kg,
            'work_type' => $this->work_type,
            'baseline_sleep_hours' => $this->baseline_sleep_hours,
            'baseline_stress_level' => $this->baseline_stress_level,
            'updated_at' => $this->updated_at,
        ];
    }
}
