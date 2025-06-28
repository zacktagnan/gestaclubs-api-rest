<?php

namespace Tests\Unit\API\V1\Management\Player\RemoveFromClub\Actions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\API\V1\Player\RemoveFromClub\Passable as RemoveFromClubPassable;
use App\Actions\API\V1\Player\RemoveFromClub\RemoveFromClubAction;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:player')]
#[Group('api:v1:unit:management:player:remove_from_club')]
#[Group('api:v1:unit:management:player:remove_from_club:actions')]
#[Group('api:v1:unit:management:player:remove_from_club:actions:remove_from_club')]
class RemoveFromClubActionTest extends UnitTestCase
{
    private int $playerSalary = 5_000_000;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear sólo lo que el test necesita
        $this->setUpClub();
        $this->setUpPlayer();

        $this->player = DataWithRelationsHelper::assignPlayerToClub(
            $this->club,
            playerSalary: $this->playerSalary,
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:player:remove_from_club:actions:remove_from_club:success')]
    public function it_removes_a_player_and_sets_salary_to_null_successfully(): void
    {
        $passable = new RemoveFromClubPassable($this->player);

        $action = new RemoveFromClubAction();

        $next = function (RemoveFromClubPassable $p) {
            return $p;
        };

        $result = $action->handle($passable, $next);

        $removedPlayer = $result->getPlayer();

        $this->assertSame(null, $removedPlayer->club_id);
        $this->assertSame(null, $removedPlayer->salary);
        $this->assertTrue($result->getPlayer()->is($removedPlayer));

        $this->assertDatabaseHas($this->playersTable, [
            'id' => $removedPlayer->id,
            'club_id' => null,
            'salary' => null,
        ]);
    }
}
