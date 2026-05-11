<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\Api\V1\AuthTokenResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\HealthProfile;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): AuthTokenResource
    {
        $payload = DB::transaction(function () use ($request) {
            /** @var User $user */
            $user = User::query()->create([
                'full_name' => $request->validated('full_name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]);

            UserSetting::query()->create([
                'user_id' => $user->id,
            ]);

            $health = $request->validated('health_profile');
            if (is_array($health) && $health !== []) {
                HealthProfile::query()->create([
                    'user_id' => $user->id,
                    'age' => $health['age'],
                    'gender' => $health['gender'] ?? null,
                    'height_cm' => $health['height_cm'],
                    'weight_kg' => $health['weight_kg'],
                    'work_type' => $health['work_type'] ?? null,
                    'baseline_sleep_hours' => $health['baseline_sleep_hours'] ?? null,
                    'baseline_stress_level' => $health['baseline_stress_level'] ?? null,
                ]);
            }

            $token = auth('api')->login($user);

            return ['token' => $token, 'user' => $user->fresh(['healthProfile', 'settings'])];
        });

        return new AuthTokenResource($payload);
    }

    public function login(LoginRequest $request): JsonResponse|AuthTokenResource
    {
        $credentials = $request->validated();

        $token = auth('api')->attempt($credentials);

        if ($token === false) {
            return response()->json(['message' => __('auth.failed')], 401);
        }

        /** @var User $user */
        $user = auth('api')->user();

        if (! $user->is_active) {
            auth('api')->logout();

            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        return new AuthTokenResource([
            'token' => $token,
            'user' => $user->load(['healthProfile', 'settings']),
        ]);
    }

    public function me(): UserResource
    {
        /** @var User $user */
        $user = auth('api')->user();

        return new UserResource($user->load(['healthProfile', 'settings']));
    }

    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json(['message' => 'Logged out.']);
    }

    public function refresh(): AuthTokenResource
    {
        $token = auth('api')->refresh();

        /** @var User $user */
        $user = auth('api')->user();

        return new AuthTokenResource([
            'token' => $token,
            'user' => $user->load(['healthProfile', 'settings']),
        ]);
    }
}
