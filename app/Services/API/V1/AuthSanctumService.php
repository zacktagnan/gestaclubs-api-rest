<?php

namespace App\Services\API\V1;

use App\Contracts\API\Auth\AuthServiceInterface;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthSanctumService implements AuthServiceInterface
{
    public function register(array $data): JsonResponse
    {
        $user = User::create($data);

        $token = $user
            ->createToken(
                data_get($data, 'device_name')
            )
            ->plainTextToken;

        return ApiResponseService::success([
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function login(array $credentials): JsonResponse
    {
        if (! Auth::attempt([
            'email' => data_get($credentials, 'email'),
            'password' => data_get($credentials, 'password'),
        ])) {
            return ApiResponseService::unauthorized();
        }

        $token = Auth::user()
            ->createToken(
                data_get($credentials, 'device_name')
            )
            ->plainTextToken;

        return ApiResponseService::success([
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return ApiResponseService::success(null, 'Logged out successfully!!');
    }
}
