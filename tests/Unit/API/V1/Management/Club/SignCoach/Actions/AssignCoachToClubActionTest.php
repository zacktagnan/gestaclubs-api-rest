<?php

namespace Tests\Unit\API\V1\Management\Club\SignCoach\Actions;

use App\Actions\API\V1\Club\SignCoach\AssignCoachToClubAction;
use App\Actions\API\V1\Club\SignCoach\Passable as ClubSignCoachPassable;
use App\Models\Coach;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_coach')]
#[Group('api:v1:unit:management:club:sign_coach:actions')]
#[Group('api:v1:unit:management:club:sign_coach:actions:assign_coach')]
class AssignCoachToClubActionTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpCoach();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_coach:actions:assign_coach:success')]
    public function it_assigns_a_coach_to_a_club_and_sets_salary(): void
    {
        $salaryToAssign = $this->club->budget - 1;

        $passable = $this->setPassableForCoachSigning(
            salary: $salaryToAssign,
        );

        $passable->setCoach($this->coach);

        $action = new AssignCoachToClubAction();

        $next = function (ClubSignCoachPassable $p) {
            return $p;
        };

        $result = $action->handle($passable, $next);

        $signedCoach = Coach::find($this->coach->id);

        $this->assertSame($this->club->id, $signedCoach->club_id);
        $this->assertSame($salaryToAssign, $signedCoach->salary);
        $this->assertTrue($result->getCoach()->is($signedCoach));

        $this->assertDatabaseHas($this->coachesTable, [
            'id' => $signedCoach->id,
            'club_id' => $this->club->id,
            'salary' => $salaryToAssign,
        ]);
    }
}
