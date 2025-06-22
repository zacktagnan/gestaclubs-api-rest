<?php

namespace Tests\Helpers\Traits;

use Illuminate\Testing\TestResponse;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;

trait RateLimitTestHelpers
{
    // protected function assertRateLimitExceeded(
    //     string $route,
    //     string $method = 'getJson',
    //     array $payload = [],
    //     string|array|null $uniqueFields = null,
    //     int $maxAttempts = 10,
    //     ?string $token = null
    // ): void {
    protected function assertRateLimitExceeded(RateLimitTestOptionsDTO $options): void
    {
        // Aplica variaciones si hay campos únicos
        $generatePayload = function (int $i) use ($options) {
            if (!$options->uniqueFields) {
                return $options->payload;
            }

            $fields = is_array($options->uniqueFields) ? $options->uniqueFields : [$options->uniqueFields];
            foreach ($fields as $field) {
                if (isset($options->payload[$field])) {
                    $options->payload[$field] = "{$i}_" . $options->payload[$field];
                }
            }

            return $options->payload;
        };

        // Lanza `maxAttempts` peticiones válidas
        for ($i = 0; $i < $options->maxAttempts; $i++) {
            $route = $options->routeGenerator
                ? ($options->routeGenerator)($i)
                : $options->route;

            $payload = $options->payloadGenerator
                ? ($options->payloadGenerator)($i)
                : $generatePayload($i);

            $request = $options->token
                ? $this->withToken($options->token)->{$options->method}($route, $payload)
                : $this->{$options->method}($route, $payload);

            $request->assertStatus($options->expectedStatus);
        }

        $finalRoute = $options->routeGenerator
            ? ($options->routeGenerator)($options->maxAttempts)
            : $options->route;

        $finalPayload = $options->payloadGenerator
            ? ($options->payloadGenerator)($options->maxAttempts)
            : $generatePayload($options->maxAttempts);

        // Lanza la petición 429 esperada
        $response = $options->token
            ? $this->withToken($options->token)->{$options->method}($finalRoute, $finalPayload)
            : $this->{$options->method}($finalRoute, $finalPayload);

        $this->assertResponseIsRateLimited($response, $options->maxAttempts);
    }

    protected function assertResponseIsRateLimited(TestResponse $response, int $expectedMax): void
    {
        $response
            ->assertTooManyRequests()
            ->assertJsonStructure([
                'status',
                'message',
                'retry_after',
                'max_attempts',
            ])
            ->assertJson([
                'status' => 'error',
                'max_attempts' => $expectedMax,
            ]);

        $json = $response->json();

        $this->assertIsString($json['message']);
        $this->assertStringContainsString('Too many attempts', $json['message']);
        $this->assertIsInt($json['retry_after']);
    }
}
