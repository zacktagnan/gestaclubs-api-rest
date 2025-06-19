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
            $request = $options->token
                ? $this->withToken($options->token)->{$options->method}($options->route, $generatePayload($i))
                : $this->{$options->method}($options->route, $generatePayload($i));

            $request->assertOk();
        }

        // Lanza la petición 429 esperada
        $response = $options->token
            ? $this->withToken($options->token)->{$options->method}($options->route, $generatePayload($options->maxAttempts))
            : $this->{$options->method}($options->route, $generatePayload($options->maxAttempts));

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
