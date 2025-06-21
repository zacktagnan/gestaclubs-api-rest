<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:clubs')]
#[Group('api:v1:management:clubs:detail')]
class DetailTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:clubs:detail:success')]
    public function a_club_can_be_retrieved(): void
    {
        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'show', $this->club))
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->club->id,
                'name' => $this->club->name,
                'budget' => $this->club->budget,
                'created_at' => $this->club->created_at->format('Y-m-d'),
            ],
        ]);
    }

    #[Test]
    #[Group('api:v1:management:clubs:detail:not_be_found')]
    public function a_club_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'show', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:management:clubs:detail:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'show', $this->club),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:management:clubs:detail:unauthenticated')]
    public function an_unauthenticated_user_cannot_retrieve_a_club(): void
    {
        $this
            ->getJson(route($this->clubsBaseRouteName . 'show', $this->club))
            ->assertUnauthorized();
    }
}
