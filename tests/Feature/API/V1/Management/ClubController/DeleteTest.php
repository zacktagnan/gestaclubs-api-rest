<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use App\Models\Club;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Helpers\Traits\RateLimitTestHelpers;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:clubs')]
#[Group('api:v1:feat:management:clubs:delete')]
class DeleteTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:clubs:delete:success')]
    public function a_club_can_be_deleted(): void
    {
        $response = $this
            // $this
            ->withToken($this->token)
            ->deleteJson(route($this->clubsBaseRouteName . 'destroy', $this->club))
            ->assertOk();

        $this->assertDatabaseMissing($this->table, [
            'id' => $response->json('data.id'),
            // o
            // 'id' => $this->club->id,
        ]);

        $this->assertDatabaseCount($this->table, 0);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:delete:not_be_found')]
    public function club_to_delete_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->clubsBaseRouteName . 'destroy', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:delete:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: '',
            routeGenerator: function (int $i): string {
                $clubToDelete = Club::factory()->create();
                return route($this->clubsBaseRouteName . 'destroy', $clubToDelete);
            },
            method: 'deleteJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:delete:unauthenticated')]
    public function an_unauthenticated_user_cannot_delete_a_club(): void
    {
        $this
            ->deleteJson(route($this->clubsBaseRouteName . 'destroy', $this->club))
            ->assertUnauthorized();
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:delete:impossible_having_members')]
    public function club_cannot_be_deleted_if_having_members(): void
    {
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: 7_000_000,
            playerSalaries: [
                4_000_000,
            ]
        );

        $this
            ->withToken($this->token)
            ->deleteJson(route($this->clubsBaseRouteName . 'destroy', $this->club))
            ->assertConflict();
    }
}
