<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin UserSetting */
class UserSettingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'theme' => $this->theme,
            'timezone' => $this->timezone,
            'notification_enabled' => $this->notification_enabled,
            'ai_auto_analysis' => $this->ai_auto_analysis,
        ];
    }
}
