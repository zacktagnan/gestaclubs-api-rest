<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\CoachDataProvider;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:coaches')]
#[Group('api:v1:feat:management:coaches:update')]
class UpdateTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:coaches:update:success')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideCoachDataToUpdate')]
    public function a_coach_can_be_updated(array $coachDataToUpdate): void
    {
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->coachesBaseRouteName . 'update', $this->coach), $coachDataToUpdate)
            ->assertOk();

        $response->assertJson([
            'data' => [
                'id' => $this->coach->id,
                'full_name' => $coachDataToUpdate['full_name'],
                'email' => $coachDataToUpdate['email'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->coach->id,
            'full_name' => $coachDataToUpdate['full_name'],
            'email' => $coachDataToUpdate['email'],
        ]);
    }

    #[Test]
    #[DataProviderExternal(CoachDataProvider::class, 'provideCoachDataToUpdate')]
    #[Group('api:v1:feat:management:coaches:update:not_be_found')]
    public function a_coach_cannot_be_found(array $coachDataToUpdate): void
    {
        $this
            ->withToken($this->token)
            ->putJson(route($this->coachesBaseRouteName . 'update', 999), $coachDataToUpdate)
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:update:malformed_request')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideInvalidCoachData')]
    public function coach_updating_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->putJson(route($this->coachesBaseRouteName . 'update', $this->coach), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:update:too_many_requests')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideCoachDataToUpdate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $coachDataToUpdate): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->coachesBaseRouteName . 'update', $this->coach),
            method: 'putJson',
            payload: $coachDataToUpdate,
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:update:unauthenticated')]
    public function an_unauthenticated_user_cannot_update_a_coach(): void
    {
        $this
            ->putJson(route($this->coachesBaseRouteName . 'update', $this->coach), [])
            ->assertUnauthorized();
    }
}
