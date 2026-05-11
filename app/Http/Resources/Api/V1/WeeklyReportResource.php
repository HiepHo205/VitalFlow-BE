<?php

namespace App\Http\Resources\Api\V1;

use App\Models\WeeklyReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WeeklyReport */
class WeeklyReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'week_start' => $this->week_start,
            'week_end' => $this->week_end,
            'summary' => $this->summary,
            'avg_mood' => $this->avg_mood,
            'avg_energy' => $this->avg_energy,
            'avg_sleep' => $this->avg_sleep,
            'burnout_risk_score' => $this->burnout_risk_score,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
