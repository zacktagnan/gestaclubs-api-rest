<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use App\Models\Club;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:management')]
#[Group('api:v1:management:clubs')]
#[Group('api:v1:management:clubs:list')]
class ListTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:management:clubs:list:success')]
    public function clubs_can_be_listed_by_authenticated_user(): void
    {
        Club::factory()->count(10)->create();

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'index'))
            ->assertOk();

        $this->assertCount(11, $response->json('data'));
        // 11: el creado desde ClubTestCase y los creados en este test

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'budget',
                    'created_at',
                ],
            ],
        ]);
    }

    #[Test]
    #[Group('api:v1:management:clubs:list:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'index'),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token
        ));
    }

    #[Test]
    #[Group('api:v1:management:clubs:list:unauthenticated')]
    public function an_unauthenticated_user_cannot_access_to_protected_clubs_list(): void
    {
        $this
            ->getJson(route($this->clubsBaseRouteName . 'index'))
            ->assertUnauthorized();
    }
}
