<?php

namespace App\Http\Resources\Api\V1;

use App\Models\DailyLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DailyLog */
class DailyLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'log_date' => $this->log_date,
            'mood_score' => $this->mood_score,
            'energy_level' => $this->energy_level,
            'stress_level' => $this->stress_level,
            'sleep_hours' => $this->sleep_hours,
            'water_intake_ml' => $this->water_intake_ml,
            'body_condition' => $this->body_condition,
            'productivity_score' => $this->productivity_score,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
