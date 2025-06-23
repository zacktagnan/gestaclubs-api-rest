<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProviders\CoachDataProvider;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:coaches')]
#[Group('api:v1:management:coaches:create')]
class CreateTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:coaches:create:success')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideCoachDataToCreate')]
    public function a_coach_can_be_created(array $coachData): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->coachesBaseRouteName . 'store'), $coachData)
            ->assertCreated();

        $response->assertJson([
            'data' => [
                'full_name' => $coachData['full_name'],
                'email' => $coachData['email'],
            ],
        ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $response->json('data.id'),
            'full_name' => $coachData['full_name'],
            'email' => $coachData['email'],
        ]);
    }

    #[Test]
    #[Group('api:v1:management:coaches:create:malformed_request')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideInvalidCoachData')]
    public function coach_creation_fails_with_invalid_data(array $invalidData, array $expectedErrors): void
    {
        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->coachesBaseRouteName . 'store'), $invalidData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors($expectedErrors);
    }

    #[Test]
    #[Group('api:v1:management:coaches:create:already_in_use')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideCoachDataToCreate')]
    public function coach_name_is_already_in_use(array $coachData): void
    {
        $this
            ->withToken($this->token)
            ->postJson(route($this->coachesBaseRouteName . 'store'), $coachData)
            ->assertCreated();

        $response = $this
            ->withToken($this->token)
            ->postJson(route($this->coachesBaseRouteName . 'store'), $coachData);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name', 'email']);

        $this->assertDatabaseCount($this->table, 2); // 2: el creado desde CoachTestCase y el creado en este test
    }

    #[Test]
    #[Group('api:v1:management:coaches:create:too_many_requests')]
    #[DataProviderExternal(CoachDataProvider::class, 'provideCoachDataToCreate')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(array $coachData): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->coachesBaseRouteName . 'store'),
            method: 'postJson',
            payload: $coachData,
            uniqueFields: ['full_name', 'email'],
            maxAttempts: 10,
            token: $this->token,
            expectedStatus: 201
        ));
    }

    #[Test]
    #[Group('api:v1:management:coaches:create:unauthenticated')]
    public function an_unauthenticated_user_cannot_create_a_coach(): void
    {
        $this
            ->postJson(route($this->coachesBaseRouteName . 'store'), [])
            ->assertUnauthorized();
    }
}
