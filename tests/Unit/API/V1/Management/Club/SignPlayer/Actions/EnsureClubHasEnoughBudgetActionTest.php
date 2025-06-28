<?php

namespace Tests\Unit\API\V1\Management\Club\SignPlayer\Actions;

use App\Actions\API\V1\Club\SignPlayer\EnsureClubHasEnoughBudgetAction;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_player')]
#[Group('api:v1:unit:management:club:sign_player:actions')]
#[Group('api:v1:unit:management:club:sign_player:actions:ensure_budget')]
class EnsureClubHasEnoughBudgetActionTest extends UnitTestCase
{
    protected EnsureClubHasEnoughBudgetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpPlayer();

        $this->action = new EnsureClubHasEnoughBudgetAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_budget:success')]
    public function it_allows_signing_when_club_has_enough_budget(): void
    {
        // Arrange
        $salary = $this->club->budget - 1;

        $passable = $this->setPassableForPlayerSigning(
            salary: $salary,
        );

        // Act
        $result = $this->action->handle($passable, fn($p) => $p);

        // Assert
        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:actions:ensure_budget:failure')]
    public function it_throws_exception_if_club_budget_is_exceeded(): void
    {
        $this->club->update(['budget' => 11_000_000]);

        // Arrange: asignamos miembros al club para consumir el presupuesto
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: 3_000_000,
            playerSalaries: [3_000_000, 3_000_000],
        );

        // Fichando un nuevo Player, sobrepasando el BUDGET para que no sea suficiente
        // $passable = new Passable([
        //     'player_id' => $this->player->id,
        //     'salary' => 4_000_000,
        //     'club' => $this->club,
        // ]);
        // o ============================================================================
        $passable = $this->setPassableForPlayerSigning(
            salary: 4_000_000,
        );

        // $this->expectException(ClubBudgetExceededException::class);
        // $this->expectExceptionMessage('Club has not enough budget for this Player signing.');
        // $this->assertDatabaseMissing($this->playersTable, [
        //     'id' => $this->player->id,
        //     'club_id' => $this->club->id,
        // ]);

        // // // Act
        // $this->action->handle($passable, fn($p) => $p);

        // o ============================================================================

        $this->expectExceptionAndAssertDatabase(
            ClubBudgetExceededException::class,
            'Club has not enough budget for this Player signing.',
            fn() => $this->assertDatabaseMissing($this->playersTable, [
                'id' => $this->player->id,
                'club_id' => $this->club->id,
            ]),
            fn() => $this->action->handle($passable, fn($p) => $p),
        );

        // o ============================================================================

        // try {
        //     $this->action->handle($passable, fn($p) => $p);
        //     $this->fail('Expected ClubBudgetExceededException was not thrown.');
        // } catch (ClubBudgetExceededException) {
        //     $this->assertDatabaseMissing($this->playersTable, [
        //         'id' => $this->player->id,
        //         'club_id' => $this->club->id,
        //     ]);
        // }
    }
}
