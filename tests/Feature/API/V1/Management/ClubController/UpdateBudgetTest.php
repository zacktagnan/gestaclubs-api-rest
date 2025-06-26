<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\ClubDataProvider;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:clubs')]
#[Group('api:v1:feat:management:clubs:update_budget')]
class UpdateBudgetTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:clubs:update_budget:success')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubBudgetToUpdate')]
    public function a_club_budget_can_be_updated(array $clubBudgetToUpdate): void
    {
        $response = $this
            ->withToken($this->token)
            ->patchJson(route($this->clubsBaseRouteName . 'updateBudget', $this->club), $clubBudgetToUpdate)
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->club->id,
                'name' => $this->club->name,
                'budget' => $clubBudgetToUpdate['budget'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->club->id,
            'name' => $response->json('data.name'),
            'budget' => $clubBudgetToUpdate['budget'],
        ]);
    }

    #[Test]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubBudgetToUpdate')]
    #[Group('api:v1:feat:management:clubs:update_budget:not_be_found')]
    public function a_club_cannot_be_found(array $clubBudgetToUpdate): void
    {
        $this
            ->withToken($this->token)
            ->patchJson(route($this->clubsBaseRouteName . 'updateBudget', 999), $clubBudgetToUpdate)
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update_budget:malformed_request')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideInvalidClubBudget')]
    public function club_budget_updating_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->patchJson(route($this->clubsBaseRouteName . 'updateBudget', $this->club), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update_budget:not_enough_budget')]
    public function club_budget_updating_fails_with_not_enough_budget(): void
    {
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            // coachSalary: 4_000_000,
            playerSalaries: [
                3_000_000,
                3_000_000,
            ]
        );

        $insufficientBudget = 9_000_006;

        $response = $this
            ->withToken($this->token)
            ->patchJson(route($this->clubsBaseRouteName . 'updateBudget', $this->club), [
                'budget' => $insufficientBudget,
            ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['budget']);

        $this->assertDatabaseHas($this->table, [
            'id' => $this->club->id,
            'budget' => $this->club->budget,
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update_budget:too_many_requests')]
    #[DataProviderExternal(ClubDataProvider::class, 'provideClubBudgetToUpdate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $clubBudgetToUpdate): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'updateBudget', $this->club),
            method: 'patchJson',
            payload: $clubBudgetToUpdate,
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:update_budget:unauthenticated')]
    public function an_unauthenticated_user_cannot_update_a_club_budget(): void
    {
        $this
            ->patchJson(route($this->clubsBaseRouteName . 'updateBudget', $this->club), [])
            ->assertUnauthorized();
    }
}
