<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:players')]
#[Group('api:v1:management:players:detail')]
class DetailTest extends PlayerTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:players:detail:success')]
    public function a_player_can_be_retrieved(): void
    {
        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->playersBaseRouteName . 'show', $this->player))
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->player->id,
                'full_name' => $this->player->full_name,
                'email' => $this->player->email,
                'created_at' => $this->player->created_at->format('Y-m-d'),
            ],
        ]);
    }

    #[Test]
    #[Group('api:v1:management:players:detail:not_be_found')]
    public function a_player_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->getJson(route($this->playersBaseRouteName . 'show', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:management:players:detail:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->playersBaseRouteName . 'show', $this->player),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:management:players:detail:unauthenticated')]
    public function an_unauthenticated_user_cannot_retrieve_a_player(): void
    {
        $this
            ->getJson(route($this->playersBaseRouteName . 'show', $this->player))
            ->assertUnauthorized();
    }
}
