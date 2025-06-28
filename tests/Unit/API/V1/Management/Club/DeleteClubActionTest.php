<?php

namespace Tests\Unit\API\V1\Management\Club;

use App\Actions\API\V1\Club\DeleteClubAction;
use App\Exceptions\API\V1\ClubHasMembersException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:delete_action')]
class DeleteClubActionTest extends UnitTestCase
{
    protected DeleteClubAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();

        $this->action = new DeleteClubAction();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:delete_action:success')]
    public function it_allows_deleting_club_when_no_have_members(): void
    {
        $this->action->execute($this->club);

        $this->assertDatabaseMissing('clubs', [
            'id' => $this->club->id,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:delete_action:has_coach')]
    public function it_throws_exception_if_club_has_coach(): void
    {
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: 500_000,
            playerSalaries: [],
        );

        $this->expectExceptionOnly(
            ClubHasMembersException::class,
            'This Club still has Players or a Coach assigned, so it cannot be deleted.',
            fn() => $this->action->execute($this->club)
        );

        $this->assertDatabaseHas($this->clubsTable, [
            'id' => $this->club->id,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:delete_action:has_players')]
    public function it_throws_exception_if_club_has_players(): void
    {
        DataWithRelationsHelper::assignPlayersStaffToClub(
            $this->club,
            playerSalaries: [1_000_000],
        );

        $this->expectExceptionOnly(
            ClubHasMembersException::class,
            'This Club still has Players or a Coach assigned, so it cannot be deleted.',
            fn() => $this->action->execute($this->club)
        );

        $this->assertDatabaseHas($this->clubsTable, [
            'id' => $this->club->id,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:delete_action:has_members')]
    public function it_throws_exception_if_club_has_members(): void
    {
        DataWithRelationsHelper::assignStaffToClub(
            $this->club,
            coachSalary: 500_000,
            playerSalaries: [1_000_000],
        );

        $this->expectExceptionOnly(
            ClubHasMembersException::class,
            'This Club still has Players or a Coach assigned, so it cannot be deleted.',
            fn() => $this->action->execute($this->club)
        );

        $this->assertDatabaseHas($this->clubsTable, [
            'id' => $this->club->id,
        ]);
    }
}
