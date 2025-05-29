<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Requests\API\V1\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;

class LoginController extends AuthController
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        return $this->authService->login($request->validated());
    }
}
