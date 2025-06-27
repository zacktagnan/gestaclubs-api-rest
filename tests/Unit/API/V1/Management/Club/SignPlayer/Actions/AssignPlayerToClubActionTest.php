<?php

namespace Tests\Unit\API\V1\Management\Club\SignPlayer\Actions;

use App\Actions\API\V1\Club\SignPlayer\AssignPlayerToClubAction;
use App\Actions\API\V1\Club\SignPlayer\Passable as ClubSignPlayerPassable;
use App\Models\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_player')]
#[Group('api:v1:unit:management:club:sign_player:actions')]
#[Group('api:v1:unit:management:club:sign_player:actions:assign_player')]
class AssignPlayerToClubActionTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpPlayer();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:assign_player:success')]
    public function it_assigns_a_player_to_a_club_and_sets_salary(): void
    {
        $salaryToAssign = $this->club->budget - 1;

        $passable = $this->setPassableForPlayerSigning(
            salary: $salaryToAssign,
        );

        $passable->setPlayer($this->player);

        $action = new AssignPlayerToClubAction();

        $next = function (ClubSignPlayerPassable $p) {
            return $p;
        };

        $result = $action->handle($passable, $next);

        $signedPlayer = Player::find($this->player->id);

        $this->assertSame($this->club->id, $signedPlayer->club_id);
        $this->assertSame($salaryToAssign, $signedPlayer->salary);
        $this->assertTrue($result->getPlayer()->is($signedPlayer));

        $this->assertDatabaseHas($this->playersTable, [
            'id' => $signedPlayer->id,
            'club_id' => $this->club->id,
            'salary' => $salaryToAssign,
        ]);
    }
}
