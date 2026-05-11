<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'health_profile' => $this->when(
                $this->relationLoaded('healthProfile'),
                $this->healthProfile ? new HealthProfileResource($this->healthProfile) : null
            ),
            'settings' => $this->when(
                $this->relationLoaded('settings'),
                $this->settings ? new UserSettingResource($this->settings) : null
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
