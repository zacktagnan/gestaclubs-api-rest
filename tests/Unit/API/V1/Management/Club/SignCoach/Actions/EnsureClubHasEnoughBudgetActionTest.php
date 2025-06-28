<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach\Actions;

use App\Actions\API\V1\Club\SignCoach\EnsureClubHasEnoughBudgetAction;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:actions')]
#[Group('api:v1:unit:management:club:sign_coach:actions:ensure_budget')]
class EnsureClubHasEnoughBudgetActionTest extends UnitTestCase
{
    protected EnsureClubHasEnoughBudgetAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();

        $this->action = new EnsureClubHasEnoughBudgetAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_budget:success')]
    public function it_allows_signing_when_club_has_enough_budget(): void
    {
        // Arrange
        $salary = $this->club->budget - 1;

        $passable = $this->setPassableForCoachSigning(
            salary: $salary,
        );

        // Act
        $result = $this->action->handle($passable, fn($p) => $p);

        // Assert
        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:ensure_budget:failure')]
    public function it_throws_exception_if_club_budget_is_exceeded(): void
    {
        $this->club->update(['budget' => 11_000_000]);

        // Arrange: asignamos miembros al club para consumir el presupuesto
        DataWithRelationsHelper::assignPlayersStaffToClub(
            $this->club,
            playerSalaries: [3_000_000, 3_000_000],
        );

        $passable = $this->setPassableForCoachSigning(
            salary: 6_000_000,
        );

        $this->expectExceptionAndAssertDatabase(
            ClubBudgetExceededException::class,
            'Club has not enough budget for this Coach signing.',
            fn() => $this->assertDatabaseMissing($this->coachesTable, [
                'id' => $this->coach->id,
                'club_id' => $this->club->id,
            ]),
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
