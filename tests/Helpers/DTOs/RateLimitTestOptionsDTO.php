<?php

namespace Tests\Helpers\DTOs;

class RateLimitTestOptionsDTO
{
    public function __construct(
        public string $route,
        public ?\Closure $routeGenerator = null,
        public string $method = 'getJson',
        public array $payload = [],
        public ?\Closure $payloadGenerator = null,
        public string|array|null $uniqueFields = null,
        public int $maxAttempts = 10,
        public int $expectedStatus = 200,
        public ?string $token = null
    ) {}
}
