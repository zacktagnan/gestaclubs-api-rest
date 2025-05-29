<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Requests\API\V1\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

class RegisterController extends AuthController
{
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        return $this->authService->register($request->validated());
    }
}
