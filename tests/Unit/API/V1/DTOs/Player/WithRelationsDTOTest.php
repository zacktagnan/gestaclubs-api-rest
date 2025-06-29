<?php

namespace Tests\Unit\API\V1\DTOs\Player;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\API\V1\UnitTestCase;
use App\DTOs\API\V1\Player\WithRelationsDTO as PlayerWithRelationsDTO;
use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;
use Tests\Helpers\DataWithRelationsHelper;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:dtos')]
#[Group('api:v1:unit:dtos:player')]
#[Group('api:v1:unit:dtos:player:with_relations_dto')]
class WithRelationsDTOTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    #[Group('api:v1:unit:dtos:player:with_relations_dto:success')]
    public function it_loads_expected_relations_on_player(): void
    {
        $totalPlayers = 4;

        ['coach' => $coach, 'player' => $player] = DataWithRelationsHelper::createClubWithRelations($totalPlayers, true);

        $result = PlayerWithRelationsDTO::from($player);

        $this->assertTrue($result->relationLoaded('club'));
        $this->assertTrue($result->club->relationLoaded('coach'));
        $this->assertTrue($result->club->coach->is($coach));

        $this->assertArrayHasKey('players_count', $result->club->getAttributes());
        $this->assertIsInt($result->club->players_count);
        $this->assertEquals($totalPlayers + 1, $result->club->players_count); // los 4 + el creado manualmente
    }

    // #[Test]
    // #[Group('api:v1:unit:dtos:player:with_relations_dto:success')]
    // public function it_loads_expected_relations_on_player(): void
    // {
    //     // Con datos directos dentro del test...

    //     $totalPlayers = 4;

    //     // Crear un club con 4 jugadores pero sin asignarles un salary, lo que no es del todo correcto
    //     $club = Club::factory()
    //         ->hasPlayers($totalPlayers)
    //         ->create();
    //     // Así mejor, además de establecer un BUDGET para el club
    //     // para que pueda llegar a cubrir las contrataciones a establecer.
    //     // Aunque, para este test, no es necesario que el club tenga un presupuesto
    //     // ni que los jugadores tengan un salario específico.
    //     // Lo importante es que el club tenga jugadores y un entrenador para la cuestión de la carga de relaciones.
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
    //     // Asignar un salario al entrenador, aunque no es necesario para este test
    //     // $coach = Coach::factory()
    //     //     ->for($club)
    //     //     ->state(['salary' => 5_000_000])
    //     //     ->create();

    //     $player = Player::factory()
    //         ->for($club)
    //         ->create();

    //     $result = PlayerWithRelationsDTO::from($player);

    //     $this->assertTrue($result->relationLoaded('club'));
    //     $this->assertTrue($result->club->relationLoaded('coach'));
    //     $this->assertTrue($result->club->coach->is($coach));

    //     $this->assertArrayHasKey('players_count', $result->club->getAttributes());
    //     $this->assertIsInt($result->club->players_count);
    //     $this->assertEquals($totalPlayers + 1, $result->club->players_count); // los 4 + el creado manualmente
    // }
}
