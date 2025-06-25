<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\ClubDataProvider;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:clubs')]
#[Group('api:v1:feat:management:clubs:create')]
class CreateTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:clubs:create:success')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubDataToCreate')]
    public function a_club_can_be_created(array $clubData): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'store'), $clubData)
            ->assertCreated();

        $response->assertJson([
            'data' => [
                'name' => $clubData['name'],
                'budget' => $clubData['budget'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            'name' => $clubData['name'],
            'budget' => $clubData['budget'],
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:create:malformed_request')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideInvalidClubData')]
    public function club_creation_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'store'), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:create:already_in_use')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubDataToCreate')]
    public function club_name_is_already_in_use(array $clubData): void
    {
        $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'store'), $clubData)
            ->assertCreated();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->clubsBaseRouteName . 'store'), $clubData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount($this->table, 2); // 2: el creado desde ClubTestCase y el creado en este test
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:create:too_many_requests')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubDataToCreate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $clubData): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'store'),
            method: 'postJson',
            payload: $clubData,
            uniqueFields: 'name',
            maxAttempts: 10,
            token: $this->token,
            expectedStatus: 201
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:create:unauthenticated')]
    public function an_unauthenticated_user_cannot_create_a_club(): void
    {
        $this
            ->postJson(route($this->clubsBaseRouteName . 'store'), [])
            ->assertUnauthorized();
    }
}
