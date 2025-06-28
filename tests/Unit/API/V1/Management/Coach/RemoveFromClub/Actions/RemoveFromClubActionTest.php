<?php

namespace Tests\Unit\API\V1\Management\Coach\RemoveFromClub\Actions;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\API\V1\Coach\RemoveFromClub\Passable as RemoveFromClubPassable;
use App\Actions\API\V1\Coach\RemoveFromClub\RemoveFromClubAction;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:coach')]
#[Group('api:v1:unit:management:coach:remove_from_club')]
#[Group('api:v1:unit:management:coach:remove_from_club:actions')]
#[Group('api:v1:unit:management:coach:remove_from_club:actions:remove_from_club')]
class RemoveFromClubActionTest extends UnitTestCase
{
    private int $coachSalary = 5_000_000;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear sólo lo que el test necesita
        $this->setUpClub();
        $this->setUpCoach();

        $this->coach = DataWithRelationsHelper::assignCoachToClub(
            $this->club,
            coachSalary: $this->coachSalary,
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:coach:remove_from_club:actions:remove_from_club:success')]
    public function it_removes_a_coach_and_sets_salary_to_null_successfully(): void
    {
        $passable = new RemoveFromClubPassable($this->coach);

        $action = new RemoveFromClubAction();

        $next = function (RemoveFromClubPassable $p) {
            return $p;
        };

        $result = $action->handle($passable, $next);

        $removedCoach = $result->getCoach();

        $this->assertSame(null, $removedCoach->club_id);
        $this->assertSame(null, $removedCoach->salary);
        $this->assertTrue($result->getCoach()->is($removedCoach));

        $this->assertDatabaseHas($this->coachesTable, [
            'id' => $removedCoach->id,
            'club_id' => null,
            'salary' => null,
        ]);
    }
}
