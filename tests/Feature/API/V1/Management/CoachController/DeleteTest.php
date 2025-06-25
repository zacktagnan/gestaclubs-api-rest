<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use App\Models\Coach;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:coaches')]
#[Group('api:v1:feat:management:coaches:delete')]
class DeleteTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:coaches:delete:success')]
    public function a_coach_can_be_deleted(): void
    {
        $response = $this
            // $this
            ->withToken($this->token)
            ->deleteJson(route($this->coachesBaseRouteName . 'destroy', $this->coach))
            ->assertOk();

        $this->assertDatabaseMissing($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->coach->id,
        ]);

        $this->assertDatabaseCount($this->table, 0);
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:delete:not_be_found')]
    public function coach_to_delete_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->coachesBaseRouteName . 'destroy', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:delete:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: '',
            routeGenerator: function (int $i): string {
                $coachToDelete = Coach::factory()->create();
                return route($this->coachesBaseRouteName . 'destroy', $coachToDelete);
            },
            method: 'deleteJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:coaches:delete:unauthenticated')]
    public function an_unauthenticated_user_cannot_delete_a_coach(): void
    {
        $this
            ->deleteJson(route($this->coachesBaseRouteName . 'destroy', $this->coach))
            ->assertUnauthorized();
    }
}
