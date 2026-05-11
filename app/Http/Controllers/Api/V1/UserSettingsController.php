<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateUserSettingsRequest;
use App\Http\Resources\Api\V1\UserSettingResource;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    public function show(Request $request): UserSettingResource
    {
        /** @var User $user */
        $user = $request->user();

        $settings = $user->settings ?? UserSetting::query()->create(['user_id' => $user->id]);

        return new UserSettingResource($settings);
    }

    public function update(UpdateUserSettingsRequest $request): UserSettingResource
    {
        /** @var User $user */
        $user = $request->user();

        $settings = $user->settings ?? new UserSetting(['user_id' => $user->id]);
        $settings->fill($request->validated());
        $settings->user_id = $user->id;
        $settings->save();

        return new UserSettingResource($settings->fresh());
    }
}
