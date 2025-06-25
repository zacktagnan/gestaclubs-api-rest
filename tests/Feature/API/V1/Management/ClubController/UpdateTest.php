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
#[Group('api:v1:feat:management:clubs:update')]
class UpdateTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:clubs:update:success')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubDataToUpdate')]
    public function a_club_can_be_updated(array $clubDataToUpdate): void
    {
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->clubsBaseRouteName . 'update', $this->club), $clubDataToUpdate)
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->club->id,
                'name' => $clubDataToUpdate['name'],
                'budget' => $clubDataToUpdate['budget'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->club->id,
            'name' => $clubDataToUpdate['name'],
            'budget' => $clubDataToUpdate['budget'],
        ]);
    }

    #[Test]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubDataToUpdate')]
    #[Group('api:v1:feat:management:clubs:update:not_be_found')]
    public function a_club_cannot_be_found(array $clubDataToUpdate): void
    {
        $this
            ->withToken($this->token)
            ->putJson(route($this->clubsBaseRouteName . 'update', 999), $clubDataToUpdate)
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update:malformed_request')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideInvalidClubData')]
    public function club_updating_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->clubsBaseRouteName . 'update', $this->club), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update:not_enough_budget')]
    public function club_updating_fails_with_not_enough_budget(): void
    {
        $this->assignStaffToClub(
            // coachSalary: 4_000_000,
            playerSalaries: [
                3_000_000,
                3_000_000,
            ]
        );

        $insufficientBudget = 9_000_006;

        // Act: intentamos actualizar con menos de 10M
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->clubsBaseRouteName . 'update', $this->club), [
                'name' => $this->club->name,
                'budget' => $insufficientBudget,
            ]);

        // Assert: debe fallar con error 422 y mensaje en 'budget'
        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['budget']);

        // Confirmar que el budget NO fue modificado en la BD
        $this->assertDatabaseHas($this->table, [
            'id' => $this->club->id,
            'budget' => $this->club->budget,
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update:too_many_requests')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubDataToUpdate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $clubDataToUpdate): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'update', $this->club),
            method: 'putJson',
            payload: $clubDataToUpdate,
            uniqueFields: 'name',
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update:unauthenticated')]
    public function an_unauthenticated_user_cannot_update_a_club(): void
    {
        $this
            ->putJson(route($this->clubsBaseRouteName . 'update', $this->club), [])
            ->assertUnauthorized();
    }
}
