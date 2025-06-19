<?php

use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->as('v1.')
    // ->middleware(ThrottleRequests::with(10, 1))
    ->middleware('throttle:test-too-many-requests')
    ->group(
        base_path('routes/api/v1.php')
    );
