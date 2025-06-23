<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:coaches')]
#[Group('api:v1:management:coaches:detail')]
class DetailTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:coaches:detail:success')]
    public function a_coach_can_be_retrieved(): void
    {
        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->coachesBaseRouteName . 'show', $this->coach))
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->coach->id,
                'full_name' => $this->coach->full_name,
                'email' => $this->coach->email,
                'created_at' => $this->coach->created_at->format('Y-m-d'),
            ],
        ]);
    }

    #[Test]
    #[Group('api:v1:management:coaches:detail:not_be_found')]
    public function a_coach_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->getJson(route($this->coachesBaseRouteName . 'show', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:management:coaches:detail:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->coachesBaseRouteName . 'show', $this->coach),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:management:coaches:detail:unauthenticated')]
    public function an_unauthenticated_user_cannot_retrieve_a_coach(): void
    {
        $this
            ->getJson(route($this->coachesBaseRouteName . 'show', $this->coach))
            ->assertUnauthorized();
    }
}
