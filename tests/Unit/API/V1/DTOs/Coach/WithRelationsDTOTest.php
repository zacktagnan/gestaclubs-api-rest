<?php

namespace Tests\Unit\API\V1\DTOs\Coach;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\API\V1\UnitTestCase;
use App\DTOs\API\V1\Coach\WithRelationsDTO as CoachWithRelationsDTO;
use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;
use Tests\Helpers\DataWithRelationsHelper;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:dtos')]
#[Group('api:v1:unit:dtos:coach')]
#[Group('api:v1:unit:dtos:coach:with_relations_dto')]
class WithRelationsDTOTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('api:v1:unit:dtos:coach:with_relations_dto:success')]
    public function it_loads_expected_relations_on_coach(): void
    {
        $totalPlayers = 2;

        ['coach' => $coach] = DataWithRelationsHelper::createClubWithRelations($totalPlayers);

        $result = CoachWithRelationsDTO::from($coach);

        $this->assertTrue($result->relationLoaded('club'));

        $this->assertArrayHasKey('players_count', $result->club->getAttributes());
        $this->assertIsInt($result->club->players_count);
        $this->assertEquals($totalPlayers, $result->club->players_count);
    }

    // #[Test]
    // #[Group('api:v1:unit:dtos:coach:with_relations_dto:success')]
    // public function it_loads_expected_relations_on_coach(): void
    // {
    //     // Con datos directos dentro del test...

    //     $totalPlayers = 2;

    //     $club = Club::factory()
    //         ->hasPlayers($totalPlayers)
    //         ->create();
    //     // $club = Club::factory()
    //     //     ->state(['budget' => 21_000_000])
    //     //     ->has(
    //     //         Player::factory()
    //     //             ->count($totalPlayers)
    //     //             ->state(['salary' => 7_000_000]),
    //     //         'players'
    //     //     )
    //     //     ->create();

    //     $coach = Coach::factory()
    //         ->for($club)
    //         ->create();
    //     // $coach = Coach::factory()
    //     //     ->for($club)
    //     //     ->state(['salary' => 5_000_000])
    //     //     ->create();

    //     $result = CoachWithRelationsDTO::from($coach);

    //     $this->assertTrue($result->relationLoaded('club'));

    //     $this->assertArrayHasKey('players_count', $result->club->getAttributes());
    //     $this->assertIsInt($result->club->players_count);
    //     $this->assertEquals($totalPlayers, $result->club->players_count);
    // }
}
