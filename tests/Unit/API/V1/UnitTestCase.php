<?php

namespace Tests\Unit\API\V1;

use App\Actions\API\V1\Club\SignCoach\Passable as SignCoachPassable;
use App\Actions\API\V1\Club\SignPlayer\Passable as SignPlayerPassable;
use Tests\TestCase;
use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;
use App\Notifications\NotifierManager;
use Mockery;
use Tests\Helpers\Traits\DataCreationForTesting;

abstract class UnitTestCase extends TestCase
{
    use DataCreationForTesting;

    protected Club $club;
    protected Coach $coach;
    protected Player $player;

    protected $coachesTable = 'coaches';
    protected $playersTable = 'players';

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function setUpClub(): void
    {
        $this->club = $this->createClub();
    }

    protected function setUpCoach(): void
    {
        $this->coach = $this->createCoach();
    }

    protected function setUpPlayer(): void
    {
        $this->player = $this->createPlayer();
    }

    protected function setPassableForCoachSigning(
        int $salary,
        ?Club $club = null,
        ?Coach $coach = null
    ): SignCoachPassable {
        return new SignCoachPassable([
            'coach_id' => ($coach ?? $this->coach)->id,
            'salary' => $salary,
            'club' => $club ?? $this->club,
        ]);
    }

    protected function setPassableForPlayerSigning(
        int $salary,
        ?Club $club = null,
        ?Player $player = null
    ): SignPlayerPassable {
        return new SignPlayerPassable([
            'player_id' => ($player ?? $this->player)->id,
            'salary' => $salary,
            'club' => $club ?? $this->club,
        ]);
    }

    protected function setNotifierManagerWithMockedNotificationChannel(
        string $channel,
        object $notifierInstance,
        callable $configureMock
    ): NotifierManager {
        $notifierMock = Mockery::mock($notifierInstance)->makePartial();

        $configureMock($notifierMock);

        return new NotifierManager([$channel => $notifierMock]);
    }

    protected function expectExceptionOnly(
        string $exceptionClass,
        string $exceptionMessage,
        callable $actionCallback
    ): void {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        $actionCallback();
    }

    protected function expectExceptionAndDatabaseMissing(
        string $exceptionClass,
        string $exceptionMessage,
        string $table,
        array $missingData,
        callable $actionCallback
    ): void {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        $this->assertDatabaseMissing($table, $missingData);

        $actionCallback();
    }
}
