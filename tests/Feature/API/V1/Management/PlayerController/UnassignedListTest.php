<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use App\Models\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:players')]
#[Group('api:v1:feat:management:players:unassigned_list')]
class UnassignedListTest extends PlayerTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:players:unassigned_list:success')]
    public function unassigned_players_can_be_listed_by_authenticated_user(): void
    {
        Player::factory()->count(10)->create();

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->playersBaseRouteName . 'unassigned-list'))
            ->assertOk();

        $this->assertCount(11, $response->json('data'));
        // 11: el creado desde PlayerTestCase y los creados en este test

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'full_name',
                    'email',
                    'salary',
                    'created_at',
                ],
            ],
        ]);

        foreach ($response->json('data') as $coach) {
            $this->assertNull($coach['salary']);
        }
    }

    #[Test]
    #[Group('api:v1:feat:management:players:unassigned_list:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->playersBaseRouteName . 'unassigned-list'),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:players:unassigned_list:unauthenticated')]
    public function an_unauthenticated_user_cannot_access_to_protected_unassigned_players_list(): void
    {
        $this
            ->getJson(route($this->playersBaseRouteName . 'unassigned-list'))
            ->assertUnauthorized();
    }
}
