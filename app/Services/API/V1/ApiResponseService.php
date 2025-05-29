<?php

namespace App\Services\API\V1;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseService
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }

    public static function notFound(string $message = 'Not Found'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }

    public static function internalServerError(string $message = 'Internal Server Error'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public static function throttled(int $maxAttempts = 60, int $retryAfter = 60): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => "Too many attempts. Please try again in {$retryAfter} seconds.",
            'max_attempts' => $maxAttempts,
            'retry_after' => $retryAfter,
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }
}
