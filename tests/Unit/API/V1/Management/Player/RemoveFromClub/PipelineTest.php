<?php

namespace Tests\Unit\API\V1\Management\Player\RemoveFromClub;

use App\Notifications\Channels\EmailNotifier;
use App\Notifications\NotifierManager;
use App\Notifications\PlayerRemovedFromClubNotification;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\API\V1\Player\RemoveFromClub\Pipeline as RemoveFromClubPipeline;
use App\Actions\API\V1\Player\RemoveFromClub\Passable as RemoveFromClubPassable;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:player')]
#[Group('api:v1:unit:management:player:remove_from_club')]
#[Group('api:v1:unit:management:player:remove_from_club:pipeline')]
class PipelineTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotifierManager $notifierManager;
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
    #[Group('api:v1:unit:management:player:remove_from_club:pipeline:success')]
    public function it_removes_a_player_successfully(): void
    {
        $this->app->instance(NotifierManager::class, $this->setNotifierManagerWithMockedNotificationChannel(
            'mail',
            new EmailNotifier(),
            function ($mock) {
                $mock->shouldReceive('notify')
                    ->once()
                    ->with(
                        Mockery::on(fn($notifiable) => $notifiable->is($this->player)),
                        Mockery::type(PlayerRemovedFromClubNotification::class)
                    );
            }
        ));

        $result = RemoveFromClubPipeline::execute($this->player);

        $this->assertInstanceOf(RemoveFromClubPassable::class, $result);

        $this->assertTrue($result->getClub()->is($this->club));
        $this->assertTrue($result->getPlayer()->is($this->player));

        $this->assertNull($result->getPlayer()->club_id);
        $this->assertNull($result->getPlayer()->salary);

        $this->assertFalse($result->getPlayer()->relationLoaded('club'));

        $this->assertDatabaseHas($this->playersTable, [
            'id' => $this->player->id,
            'club_id' => null,
            'salary' => null,
        ]);
    }

    #[Test]
    #[Group('api:v1:unit:management:player:remove_from_club:pipeline:notification_failure')]
    public function it_throws_if_notification_fails(): void
    {
        $this->app->instance(NotifierManager::class, $this->setNotifierManagerWithMockedNotificationChannel(
            'mail',
            new EmailNotifier(),
            function ($mock) {
                $mock->shouldReceive('notify')
                    // ->once()
                    ->andThrow(new \RuntimeException('Fake notification failure.'));
            }
        ));

        $this->expectExceptionAndAssertDatabase(
            ErrorSendingNotificationException::class,
            'Failed to send notification to removed Player: Fake notification failure.',
            fn() => $this->assertDatabaseHas($this->playersTable, [
                'id' => $this->player->id,
                'club_id' => $this->club->id,
                'salary' => $this->playerSalary,
            ]),
            fn() => RemoveFromClubPipeline::execute($this->player)
        );
    }
}
