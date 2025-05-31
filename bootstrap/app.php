<?php

use App\Exceptions\CoachAlreadyAssignedException;
use App\Exceptions\PlayerAlreadyAssignedException;
use Illuminate\Foundation\Application;
use App\Services\API\V1\ApiResponseService;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ThrottleRequestsException $exception) {
            $retryAfter = data_get($exception->getHeaders(), 'Retry-After', 60);
            $maxAttempts = data_get($exception->getHeaders(), 'X-RateLimit-Limit', 60);

            return ApiResponseService::throttled(
                maxAttempts: $maxAttempts,
                retryAfter: $retryAfter
            );
        });

        $exceptions->render(function (NotFoundHttpException $exception) {
            return ApiResponseService::notFound(
                message: $exception->getMessage() ?: 'Resource not found.'
            );
        });

        $exceptions->render(function (PlayerAlreadyAssignedException $exception) {
            return ApiResponseService::unprocessableEntity(
                message: $exception->getMessage() ?: 'Entity is unprocessable.'
            );
        });

        $exceptions->render(function (CoachAlreadyAssignedException $exception) {
            return ApiResponseService::unprocessableEntity(
                message: $exception->getMessage() ?: 'Entity is unprocessable.'
            );
        });
    })->create();
