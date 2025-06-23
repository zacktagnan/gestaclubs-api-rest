<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use App\Models\Coach;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:coaches')]
#[Group('api:v1:management:coaches:list')]
class ListTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:coaches:list:success')]
    public function coaches_can_be_listed_by_authenticated_user(): void
    {
        Coach::factory()->count(10)->create();

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->coachesBaseRouteName . 'index'))
            ->assertOk();

        $this->assertCount(11, $response->json('data'));
        // 11: el creado desde CoachTestCase y los creados en este test

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
    }

    #[Test]
    #[Group('api:v1:management:coaches:list:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->coachesBaseRouteName . 'index'),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token
        ));
    }

    #[Test]
    #[Group('api:v1:management:coaches:list:unauthenticated')]
    public function an_unauthenticated_user_cannot_access_to_protected_coaches_list(): void
    {
        $this
            ->getJson(route($this->coachesBaseRouteName . 'index'))
            ->assertUnauthorized();
    }
}
