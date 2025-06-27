<?php

namespace Tests\Unit\API\V1\Management\Club\SignPlayer;

use Mockery;
use App\Models\Club;
use Tests\Unit\API\V1\UnitTestCase;
use App\Notifications\NotifierManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Exceptions\API\V1\ClubBudgetExceededException;
use App\Notifications\PlayerAssignedToClubNotification;
use App\Exceptions\API\V1\PlayerAlreadyAssignedException;
use App\Actions\API\V1\Club\SignPlayer\Pipeline as ClubSignPlayerPipeline;
use App\Notifications\Channels\EmailNotifier;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:club')]
#[Group('api:v1:unit:management:club:sign_player')]
#[Group('api:v1:unit:management:club:sign_player:pipeline')]
class PipelineTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear sólo lo que el test necesita
        $this->setUpClub();
        $this->setUpPlayer();
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:pipeline:success')]
    public function it_signs_a_player_successfully(): void
    {
        // Mock notifier to avoid real notification
        // $notifierMock = Mockery::mock(NotifierManager::class);
        // $notifierMock->shouldReceive('notify')->once()->with(
        //     Mockery::on(fn($notifiable) => $notifiable->is($this->player)),
        //     Mockery::type(PlayerAssignedToClubNotification::class)
        // );
        // $this->app->instance(NotifierManager::class, $notifierMock);

        $emailNotifierInstance = new EmailNotifier();
        $emailNotifierMock = Mockery::mock($emailNotifierInstance)->makePartial();
        $emailNotifierMock
            ->shouldReceive('notify')
            ->once()
            ->with(
                Mockery::on(fn($notifiable) => $notifiable->is($this->player)),
                Mockery::type(PlayerAssignedToClubNotification::class)
            );

        $this->app->instance(NotifierManager::class, new NotifierManager([
            'mail' => $emailNotifierMock,
        ]));

        $salaryToAssign = $this->club->budget - 1;

        $result = ClubSignPlayerPipeline::execute([
            'player_id' => $this->player->id,
            'salary' => $salaryToAssign,
            'club' => $this->club,
        ]);

        $signedPlayer = $result->getPlayer();

        $this->assertTrue($signedPlayer->is($this->player));
        $this->assertEquals($this->club->id, $signedPlayer->club_id);
        $this->assertEquals($salaryToAssign, $signedPlayer->salary);

        $this->assertDatabaseHas($this->playersTable, [
            'id' => $signedPlayer->id,
            'club_id' => $this->club->id,
            'salary' => $salaryToAssign,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:pipeline:already_assigned')]
    public function it_throws_exception_if_player_is_already_assigned(): void
    {
        $otherClub = Club::factory()->create();
        $player = $this->createPlayerAssignedToClub(
            club: $otherClub,
            playerSalary: $otherClub->budget - 1,
        );

        $this->expectException(PlayerAlreadyAssignedException::class);

        ClubSignPlayerPipeline::execute([
            'player_id' => $player->id,
            'salary' => $this->club->budget - 1,
            'club' => $this->club,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:pipeline:not_enough')]
    public function it_throws_exception_if_club_has_not_enough_budget(): void
    {
        $this->expectException(ClubBudgetExceededException::class);

        ClubSignPlayerPipeline::execute([
            'player_id' => $this->player->id,
            'salary' => $this->club->budget + 1,
            'club' => $this->club,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:club:sign_player:pipeline:notification_failure')]
    public function it_throws_if_notification_fails(): void
    {
        // $mock = Mockery::mock(NotifierManager::class);
        // $mock->shouldReceive('notify')->andThrow(new \RuntimeException('Fake notification failure.'));
        // $this->app->instance(NotifierManager::class, $mock);

        $emailNotifierInstance = new EmailNotifier();
        $emailNotifierMock = Mockery::mock($emailNotifierInstance)->makePartial();
        $emailNotifierMock
            ->shouldReceive('notify')
            // ->once()
            ->andThrow(new \RuntimeException('Fake notification failure.'));

        $this->app->instance(NotifierManager::class, new NotifierManager([
            'mail' => $emailNotifierMock,
        ]));


        $this->expectExceptionMessage('Failed to send notification to assigned Player: Fake notification failure.');

        ClubSignPlayerPipeline::execute([
            'player_id' => $this->player->id,
            'salary' => $this->club->budget - 1,
            'club' => $this->club,
        ]);
    }
}
