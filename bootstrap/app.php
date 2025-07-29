<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Middleware\HandleCors;
use App\Services\API\V1\ApiResponseService;
use Symfony\Component\HttpFoundation\Response;
use App\Exceptions\API\V1\ClubHasMembersException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(HandleCors::class);
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

        $exceptions->render(function (ClubHasMembersException $exception) {
            return ApiResponseService::error(
                message: $exception->getMessage() ?: 'Conflict with the current state of the target resource.',
                code: Response::HTTP_CONFLICT,
            );
        });

        $exceptions->render(function (ErrorSendingNotificationException $exception) {
            return ApiResponseService::internalServerError(
                message: $exception->getMessage() ?: 'Internal Server Error.'
            );
        });
    })->create();
