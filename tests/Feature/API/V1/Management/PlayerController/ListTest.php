<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use App\Models\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\DataCreationForTesting;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:players')]
#[Group('api:v1:feat:management:players:list')]
class ListTest extends PlayerTestCase
{
    use DataCreationForTesting, RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:players:list:success')]
    public function players_can_be_listed_by_authenticated_user(): void
    {
        Player::factory()->count(10)->create();

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->playersBaseRouteName . 'index'))
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

        foreach ($response->json('data') as $player) {
            // -> si, dentro del PlayerResource:
            // 'salary' => $this->salary, // con posibilidad de que sea NULL
            // $this->assertNull($player['salary']);
            // -> si, dentro del PlayerResource:
            // 'salary' => $this->salary ?? 0, // si es NULL, se establecerá un 0
            $this->assertEquals(0, $player['salary']);
        }
    }

    #[Test]
    #[Group('api:v1:feat:management:players:list:success_by_full_name')]
    public function players_can_be_listed_and_filtered_by_full_name(): void
    {
        $this->createPlayersOnlyWithFullName([
            'Juan García',
            'Pedro García',
            'Ana López',
        ]);

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->playersBaseRouteName . 'index', ['full_name' => 'García']))
            ->assertOk();

        $this->assertCount(2, $response->json('data'));

        foreach ($response->json('data') as $player) {
            $this->assertStringContainsString('García', $player['full_name']);
        }
    }

    #[Test]
    #[Group('api:v1:feat:management:players:list:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->playersBaseRouteName . 'index'),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:players:list:unauthenticated')]
    public function an_unauthenticated_user_cannot_access_to_protected_players_list(): void
    {
        $this
            ->getJson(route($this->playersBaseRouteName . 'index'))
            ->assertUnauthorized();
    }
}
