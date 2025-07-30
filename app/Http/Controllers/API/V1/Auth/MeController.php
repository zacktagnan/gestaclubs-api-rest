<?php

namespace App\Http\Controllers\API\V1\Auth;

use Illuminate\Http\JsonResponse;

class MeController extends AuthController
{
    public function __invoke(): JsonResponse
    {
        return $this->authService->me();
    }
}
