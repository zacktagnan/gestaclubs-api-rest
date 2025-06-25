<?php

namespace Tests\Feature\API\V1\Management\PlayerController;

use App\Models\Club;
use App\Models\Player;
use App\Notifications\Channels\EmailNotifier;
use App\Notifications\NotifierManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:players')]
#[Group('api:v1:feat:management:players:remove_from_club')]
class RemoveFromClubTest extends PlayerTestCase
{
    use RateLimitTestHelpers;

    protected Club $club;
    protected int $validSalaryToAssign;
    protected Player $playerToRemove;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::factory()->create();
        $this->validSalaryToAssign = $this->club->budget - 1;

        $this->playerToRemove = Player::factory()->for($this->club)->create([
            'salary' => $this->validSalaryToAssign,
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:management:players:remove_from_club:success')]
    public function a_player_can_be_removed_from_a_club(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->playersBaseRouteName . 'removeFromClub', $this->playerToRemove))
            ->assertOk();

        $this->assertDatabaseHas($this->table, [
            'id' => $this->playerToRemove->id,
            'salary' => null,
            'club_id' => null,
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:management:players:remove_from_club:not_be_found')]
    public function player_ro_remove_cannot_be_found(): void
    {
        $this
            ->withToken($this->token)
            ->deleteJson(route($this->playersBaseRouteName . 'removeFromClub', 999))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:players:remove_from_club:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: '',
            routeGenerator: function (int $i): string {
                $playerToRemove = Player::factory()->for($this->club)->create([
                    'salary' => $this->validSalaryToAssign,
                ]);
                return route($this->playersBaseRouteName . 'removeFromClub', $playerToRemove);
            },
            method: 'deleteJson',
            payload: [],
            uniqueFields: null,
            maxAttempts: 10,
            token: $this->token,
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:players:remove_from_club:unauthenticated')]
    public function an_unauthenticated_user_cannot_remove_a_player_from_a_club(): void
    {
        $this
            ->deleteJson(route($this->playersBaseRouteName . 'removeFromClub', $this->playerToRemove))
            ->assertUnauthorized();
    }

    #[Test]
    #[Group('api:v1:feat:management:players:remove_from_club:notification_failure')]
    public function error_sending_notification_after_remove_a_player_from_a_club(): void
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
            ->deleteJson(route($this->playersBaseRouteName . 'removeFromClub', $this->playerToRemove));

        $response
            ->assertInternalServerError()
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment([
                'message' => 'Failed to send notification to removed Player: Fake notification failure.',
            ]);

        $this->assertDatabaseHas($this->table, [
            'id' => $this->playerToRemove->id,
            'club_id' => $this->club->id,
        ]);
    }
}
