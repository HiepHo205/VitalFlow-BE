<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateHealthProfileRequest;
use App\Http\Resources\Api\V1\HealthProfileResource;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthProfileController extends Controller
{
    public function show(Request $request): HealthProfileResource|JsonResponse
    {
        $profile = $request->user()->healthProfile;

        if ($profile === null) {
            return response()->json(['message' => 'Health profile not found.'], 404);
        }

        return new HealthProfileResource($profile);
    }

    public function upsert(UpdateHealthProfileRequest $request): HealthProfileResource
    {
        /** @var User $user */
        $user = $request->user();

        $profile = HealthProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return new HealthProfileResource($profile);
    }
}
