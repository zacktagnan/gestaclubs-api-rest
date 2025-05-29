<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\API\V1\Controller;
use App\Contracts\API\Auth\AuthServiceInterface;

class AuthController extends Controller
{
    public function __construct(protected readonly AuthServiceInterface $authService)
    {
        parent::__construct();
    }
}
