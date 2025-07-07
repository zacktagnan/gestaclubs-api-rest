<?php

namespace Tests\Feature\API\V1\Management\ClubController;

use App\Models\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Helpers\DTOs\RateLimitTestOptionsDTO;
use Tests\Helpers\Traits\RateLimitTestHelpers;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:management')]
#[Group('api:v1:feat:management:clubs')]
#[Group('api:v1:feat:management:clubs:players_list')]
class PlayersListTest extends ClubTestCase
{
    use RateLimitTestHelpers;

    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:success')]
    public function it_lists_players_of_a_club(): void
    {
        DataWithRelationsHelper::assignPlayersStaffWithDataToClub(
            $this->club,
            playersData: [
                ['full_name' => 'Juan Carlos', 'salary' => 7_000_000],
                ['full_name' => 'Pepe García', 'salary' => 7_000_000],
                ['full_name' => 'Laura Gómez', 'salary' => 7_000_000],
            ],
        );
        // o
        // Player::factory()->create(['club_id' => $this->club->id, 'full_name' => 'Juan Carlos']);
        // Player::factory()->create(['club_id' => $this->club->id, 'full_name' => 'Pepe García']);
        // Player::factory()->create(['club_id' => $this->club->id, 'full_name' => 'Laura Gómez']);

        // Creando jugadores NO asignados
        Player::factory()->count(5)->create();

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'players', ['club' => $this->club->id]))
            ->assertOk();

        $this->assertCount(3, $response->json('data'));

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
    #[Group('api:v1:feat:management:clubs:players_list:success_by_filter')]
    public function it_can_filter_players_by_full_name_within_a_club(): void
    {
        DataWithRelationsHelper::assignPlayersStaffWithDataToClub(
            $this->club,
            playersData: [
                ['full_name' => 'Carlos Vela', 'salary' => 7_000_000],
                ['full_name' => 'Velasco Torres', 'salary' => 7_000_000],
                ['full_name' => 'Miguel Ángel', 'salary' => 7_000_000],
            ],
        );
        // o
        // Player::factory()->create(['club_id' => $this->club->id, 'full_name' => 'Carlos Vela']);
        // Player::factory()->create(['club_id' => $this->club->id, 'full_name' => 'Velasco Torres']);
        // Player::factory()->create(['club_id' => $this->club->id, 'full_name' => 'Miguel Ángel']);

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'players', [
                'club' => $this->club->id,
                'full_name' => 'Vela'
            ]))
            ->assertOk();

        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals('Carlos Vela', $data[0]['full_name']);
        $this->assertEquals('Velasco Torres', $data[1]['full_name']);
    }


    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:salary_range')]
    public function it_filters_players_by_salary_range(): void
    {
        Player::factory()->create(['club_id' => $this->club->id, 'salary' => 500_000]);
        Player::factory()->create(['club_id' => $this->club->id, 'salary' => 1_000_000]);
        Player::factory()->create(['club_id' => $this->club->id, 'salary' => 1_500_000]);

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'players', [
                'club' => $this->club->id,
                'salary_min' => 750000,
                'salary_max' => 1250000
            ]))
            ->assertOk();

        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertEquals(1_000_000, $data[0]['salary']);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:sort')]
    public function it_sorts_players_by_salary_desc(): void
    {
        Player::factory()->create(['club_id' => $this->club->id, 'salary' => 750_000]);
        Player::factory()->create(['club_id' => $this->club->id, 'salary' => 1_250_000]);
        Player::factory()->create(['club_id' => $this->club->id, 'salary' => 500_000]);

        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'players', [
                'club' => $this->club->id,
                'sort_by' => 'salary',
                'sort' => 'desc'
            ]))
            ->assertOk();

        $salaries = array_column($response->json('data'), 'salary');

        $this->assertSame([1_250_000, 750_000, 500_000], $salaries);
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:empty')]
    public function it_returns_empty_when_club_has_no_players(): void
    {
        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'players', ['club' => $this->club]))
            ->assertOk();

        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:club_not_exist')]
    public function players_club_does_not_exist(): void
    {
        $this
            ->withToken($this->token)
            ->getJson(route($this->clubsBaseRouteName . 'players', ['club' => 9999]))
            ->assertNotFound();
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:too_many_requests')]
    public function it_returns_rate_limit_exceeded_when_too_many_requests(): void
    {
        $this->assertRateLimitExceeded(new RateLimitTestOptionsDTO(
            route: route($this->clubsBaseRouteName . 'players', ['club' => $this->club->id]),
            method: 'getJson',
            payload: [],
            token: $this->token,
            maxAttempts: 10
        ));
    }

    #[Test]
    #[Group('api:v1:feat:management:clubs:players_list:unauthenticated')]
    public function an_unauthenticated_user_cannot_access_players_of_club(): void
    {
        $this
            ->getJson(route($this->clubsBaseRouteName . 'players', ['club' => $this->club->id]))
            ->assertUnauthorized();
    }
}
