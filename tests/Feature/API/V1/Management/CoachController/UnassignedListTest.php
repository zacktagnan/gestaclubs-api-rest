<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use App\Models\Coach;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:coaches')]
#[Group('api:v1:feat:management:coaches:unassigned_list')]
class UnassignedListTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:coaches:unassigned_list:success')]
    public function unassigned_coaches_can_be_listed_by_authenticated_user(): void
    {
        Coach::factory()->count(10)->create();

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->coachesBaseRouteName . 'unassigned-list'))
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

        foreach ($response->json('data') as $coach) {
            $this->assertNull($coach['salary']);
            // $this->assertNull($coach['club_id']);
            // Y más claves si lo deseas...
        }
        // o
        // $this->assertTrue(
        //     collect($response->json('data'))->every(
        //         fn($coach) =>
        //         is_null($coach['salary']) &&
        //             is_null($coach['club_id'])
        //         // && ... otras condiciones
        //     )
        // );
        // // o
        // $coaches = collect($response->json('data'));
        // $this->assertTrue($coaches->every(fn ($coach) => is_null($coach['salary'])));
        // $this->assertTrue($coaches->every(fn ($coach) => is_null($coach['club_id'])));
        // // Y más claves si lo deseas...
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:unassigned_list:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->coachesBaseRouteName . 'unassigned-list'),
            method: 'getJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:unassigned_list:unauthenticated')]
    public function an_unauthenticated_user_cannot_access_to_protected_unassigned_coaches_list(): void
    {
        $this
            ->getJson(route($this->coachesBaseRouteName . 'unassigned-list'))
            ->assertUnauthorized();
    }
}
