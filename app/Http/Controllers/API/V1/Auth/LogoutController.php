<?php

namespace App\Http\Controllers\API\V1\Auth;

use Illuminate\Http\JsonResponse;

class LogoutController extends AuthController
{
    public function __invoke(): JsonResponse
    {
        return $this->authService->logout();
    }
}
