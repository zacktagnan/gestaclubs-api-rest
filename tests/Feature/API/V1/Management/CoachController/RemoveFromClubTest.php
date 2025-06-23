<?php

namespace Tests\Feature\API\V1\Management\CoachController;

use App\Models\Club;
use App\Models\Coach;
use App\Notifications\Channels\EmailNotifier;
use App\Notifications\NotifierManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

class RemoveFromClubTest extends CoachTestCase
{
    use RateLimitTestHelpers;

    protected Club $club;
    protected int $validSalaryToAssign;
    protected Coach $coachToRemove;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::factory()->create();
        $this->validSalaryToAssign = $this->club->budget - 1;

        $this->coachToRemove = Coach::factory()->for($this->club)->create([
            'salary' => $this->validSalaryToAssign,
        ]);
    }

    #[Test]
    #[Group('api:v1:management:coaches:remove_from_club:success')]
    public function a_coach_can_be_removed_from_a_club(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->coachesBaseRouteName . 'removeFromClub', $this->coachToRemove))
            ->assertOk();

        $this->assertDatabaseHas($this->table, [
            'id' => $this->coachToRemove->id,
            'salary' => null,
            'club_id' => null,
        ]);
    }

    #[Test]
    #[Group('api:v1:management:coaches:remove_from_club:not_be_found')]
    public function coach_ro_remove_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->coachesBaseRouteName . 'removeFromClub', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:management:coaches:remove_from_club:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: '',
            routeGenerator: function (int $i): string {
                $coachToRemove = Coach::factory()->for($this->club)->create([
                    'salary' => $this->validSalaryToAssign,
                ]);
                return route($this->coachesBaseRouteName . 'removeFromClub', $coachToRemove);
            },
            method: 'deleteJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:management:coaches:remove_from_club:unauthenticated')]
    public function an_unauthenticated_user_cannot_remove_a_coach_from_a_club(): void
    {
        $this
            ->deleteJson(route($this->coachesBaseRouteName . 'removeFromClub', $this->coachToRemove))
            ->assertUnauthorized();
    }

    #[Test]
    #[Group('api:v1:management:coaches:remove_from_club:notification_failure')]
    public function error_sending_notification_after_remove_a_coach_from_a_club(): void
    {
        $emailNotifierInstance = new EmailNotifier();
        $emailNotifierMock = \Mockery::mock($emailNotifierInstance)->makePartial();
        $emailNotifierMock
            ->shouldReceive('notify')
            ->once()
            ->andThrow(new \RuntimeException('Fake notification failure.'));

        $this->app->instance(NotifierManager::class, new NotifierManager([
            'mail' => $emailNotifierMock,
        ]));

        $response = $this
            ->withToken($this->token)
            ->deleteJson(route($this->coachesBaseRouteName . 'removeFromClub', $this->coachToRemove));

        $response
            ->assertInternalServerError()
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment([
                'message' => 'Failed to send notification to removed Coach: Fake notification failure.',
            ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $this->coachToRemove->id,
            'club_id' => $this->club->id,
        ]);
    }
}
