<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use App\Models\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:players')]
#[Group('api:v1:management:players:delete')]
class DeleteTest extends PlayerTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:players:delete:success')]
    public function a_player_can_be_deleted(): void
    {
        $response = $this
            // $this
            ->withToken($this->token)
            ->deleteJson(route($this->playersBaseRouteName . 'destroy', $this->player))
            ->assertOk();

        $this->assertDatabaseMissing($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->player->id,
        ]);

        $this->assertDatabaseCount($this->table, 0);
    }

    #[Test]
    #[Group('api:v1:management:players:delete:not_be_found')]
    public function player_to_delete_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->playersBaseRouteName . 'destroy', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:management:players:delete:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: '',
            routeGenerator: function (int $i): string {
                $playerToDelete = Player::factory()->create();
                return route($this->playersBaseRouteName . 'destroy', $playerToDelete);
            },
            method: 'deleteJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:management:players:delete:unauthenticated')]
    public function an_unauthenticated_user_cannot_delete_a_player(): void
    {
        $this
            ->deleteJson(route($this->playersBaseRouteName . 'destroy', $this->player))
            ->assertUnauthorized();
    }
}
