<?php

namespace Tests\Helpers\DTOs;

class RateLimitTestOptionsDTO
{
    public function __construct(
        public string $route,
        public string $method = 'getJson',
        public array $payload = [],
        public string|array|null $uniqueFields = null,
        public int $maxAttempts = 10,
        public int $expectedStatus = 200,
        public ?string $token = null
    ) {}
}
