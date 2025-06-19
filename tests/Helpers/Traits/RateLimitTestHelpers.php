<?php

namespace Tests\Helpers\Traits;

use Illuminate\Testing\TestResponse;

trait RateLimitTestHelpers
{
    protected function assertRateLimitExceeded(
        string $route,
        string $method = 'getJson',
        array $payload = [],
        string|array|null $uniqueFields = null,
        int $maxAttempts = 10
    ): void {
        // Aplica variaciones si hay campos únicos
        $generatePayload = function (int $i) use ($payload, $uniqueFields) {
            if (!$uniqueFields) {
                return $payload;
            }

            $fields = is_array($uniqueFields) ? $uniqueFields : [$uniqueFields];
            foreach ($fields as $field) {
                if (isset($payload[$field])) {
                    $payload[$field] = "{$i}_" . $payload[$field];
                }
            }

            return $payload;
        };

        // Lanza `maxAttempts` peticiones válidas
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this
                ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->{$method}($route, $generatePayload($i))
                ->assertOk();
        }

        // Lanza la petición 429 esperada
        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->{$method}($route, $generatePayload($maxAttempts));

        $this->assertResponseIsRateLimited($response, $maxAttempts);
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
