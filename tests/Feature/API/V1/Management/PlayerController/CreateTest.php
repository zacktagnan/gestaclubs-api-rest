<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\PlayerDataProvider;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:players')]
#[Group('api:v1:management:players:create')]
class CreateTest extends PlayerTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:players:create:success')]
    #[DataProviderExternal(PlayerDataProvider::class, 'providePlayerDataToCreate')]
    public function a_player_can_be_created(array $playerData): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->playersBaseRouteName . 'store'), $playerData)
            ->assertCreated();

        $response->assertJson([
            'data' => [
                'full_name' => $playerData['full_name'],
                'email' => $playerData['email'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            'full_name' => $playerData['full_name'],
            'email' => $playerData['email'],
        ]);
    }

    #[Test]
    #[Group('api:v1:management:players:create:malformed_request')]
    #[DataProviderExternal(PlayerDataProvider::class, 'provideInvalidPlayerData')]
    public function player_creation_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->playersBaseRouteName . 'store'), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:management:players:create:already_in_use')]
    #[DataProviderExternal(PlayerDataProvider::class, 'providePlayerDataToCreate')]
    public function player_name_is_already_in_use(array $playerData): void
    {
        $this
            ->withToken($this->token)
            ->postJson(route($this->playersBaseRouteName . 'store'), $playerData)
            ->assertCreated();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->playersBaseRouteName . 'store'), $playerData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'email']);

        $this->assertDatabaseCount($this->table, 2); // 2: el creado desde PLayerTestCase y el creado en este test
    }

    #[Test]
    #[Group('api:v1:management:players:create:too_many_requests')]
    #[DataProviderExternal(PlayerDataProvider::class, 'providePlayerDataToCreate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $playerData): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->playersBaseRouteName . 'store'),
            method: 'postJson',
            payload: $playerData,
            uniqueFields: ['full_name', 'email'],
            maxAttempts: 10,
            token: $this->token,
            expectedStatus: 201
        ));
    }

    #[Test]
    #[Group('api:v1:management:players:create:unauthenticated')]
    public function an_unauthenticated_user_cannot_create_a_player(): void
    {
        $this
            ->postJson(route($this->playersBaseRouteName . 'store'), [])
            ->assertUnauthorized();
    }
}
