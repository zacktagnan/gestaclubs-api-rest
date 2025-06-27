<?php

namespace Tests\Unit\API\V1\Management\Club\SignPlayer\Actions;

use App\Actions\API\V1\Club\SignPlayer\EnsurePlayerIsNotAlreadyAssignedAction;
use App\Actions\API\V1\Club\SignPlayer\Passable as ClubSignPlayerPassable;
use App\Exceptions\API\V1\PlayerAlreadyAssignedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_player')]
#[Group('api:v1:unit:management:club:sign_player:actions')]
#[Group('api:v1:unit:management:club:sign_player:actions:ensure_not_assigned')]
class EnsurePlayerIsNotAlreadyAssignedActionTest extends UnitTestCase
{
    protected EnsurePlayerIsNotAlreadyAssignedAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpPlayer();

        $this->action = new EnsurePlayerIsNotAlreadyAssignedAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_not_assigned:success')]
    public function it_allows_player_without_club(): void
    {
        $passable = $this->setPassableForPlayerSigning(
            salary: $this->club->budget - 1,
        );

        $next = fn(ClubSignPlayerPassable $p) => $p;

        $result = $this->action->handle($passable, $next);

        $this->assertTrue($result->getPlayer()->is($this->player));
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_not_assigned:already_in_same')]
    public function it_throws_if_player_already_belongs_to_the_same_club(): void
    {
        // Asignamos manualmente al mismo club
        $this->player->club()->associate($this->club)->save();

        $passable = $this->setPassableForPlayerSigning(
            salary: $this->club->budget - 1,
        );

        // $this->expectException(PlayerAlreadyAssignedException::class);
        // $this->expectExceptionMessage("This Player is already assigned to this Club ({$this->club->name}).");

        // $this->action->handle($passable, fn() => null);

        // o ============================================================================

        $this->expectExceptionOnly(
            PlayerAlreadyAssignedException::class,
            "This Player is already assigned to this Club ({$this->club->name}).",
            fn() => $this->action->handle($passable, fn() => null)
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_not_assigned:already_in_another')]
    public function it_throws_if_player_belongs_to_another_club(): void
    {
        $anotherClub = $this->createClub();

        $this->player->club()->associate($anotherClub)->save();

        $passable = $this->setPassableForPlayerSigning(
            salary: $this->club->budget - 1,
        );

        // $this->expectException(PlayerAlreadyAssignedException::class);
        // $this->expectExceptionMessage("This Player is already assigned to another Club ({$anotherClub->name}).");

        // $this->action->handle($passable, fn() => null);

        // o ============================================================================

        $this->expectExceptionAndDatabaseMissing(
            PlayerAlreadyAssignedException::class,
            "This Player is already assigned to another Club ({$anotherClub->name}).",
            $this->playersTable,
            [
                'id' => $this->player->id,
                'club_id' => $this->club->id,
            ],
            fn() => $this->action->handle($passable, fn() => null),
        );
    }
}
