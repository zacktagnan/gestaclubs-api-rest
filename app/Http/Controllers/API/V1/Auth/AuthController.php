<?php

namespace App\Http\Controllers\API\V1\Auth;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\API\V1\Controller;
use App\Contracts\API\Auth\AuthServiceInterface;

class AuthController extends Controller
{
    public function __construct(protected readonly AuthServiceInterface $authService)
    {
        parent::__construct();
    }

    public function me(): JsonResponse
    {
        return $this->authService->me();
    }
}
