<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\PlayerDataProvider;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:players')]
#[Group('api:v1:feat:management:players:update')]
class UpdateTest extends PlayerTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:players:update:success')]
    #[DataProviderExternal(PlayerDataProvider::class, 'providePlayerDataToUpdate')]
    public function a_player_can_be_updated(array $playerDataToUpdate): void
    {
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->playersBaseRouteName . 'update', $this->player), $playerDataToUpdate)
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->player->id,
                'full_name' => $playerDataToUpdate['full_name'],
                'email' => $playerDataToUpdate['email'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->player->id,
            'full_name' => $playerDataToUpdate['full_name'],
            'email' => $playerDataToUpdate['email'],
        ]);
    }

    #[Test]
    #[DataProviderExternal(PlayerDataProvider::class, 'providePlayerDataToUpdate')]
    #[Group('api:v1:feat:management:players:update:not_be_found')]
    public function a_player_cannot_be_found(array $playerDataToUpdate): void
    {
        $this
            ->withToken($this->token)
            ->putJson(route($this->playersBaseRouteName . 'update', 999), $playerDataToUpdate)
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:players:update:malformed_request')]
    #[DataProviderExternal(PlayerDataProvider::class, 'provideInvalidPlayerData')]
    public function player_updating_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->playersBaseRouteName . 'update', $this->player), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:feat:management:players:update:too_many_requests')]
    #[DataProviderExternal(PlayerDataProvider::class, 'providePlayerDataToUpdate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $playerDataToUpdate): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->playersBaseRouteName . 'update', $this->player),
            method: 'putJson',
            payload: $playerDataToUpdate,
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:players:update:unauthenticated')]
    public function an_unauthenticated_user_cannot_update_a_player(): void
    {
        $this
            ->putJson(route($this->playersBaseRouteName . 'update', $this->player), [])
            ->assertUnauthorized();
    }
}
