<?php

namespace Tests\Unit\API\V1\Management\Player\RemoveFromClub\Actions;

use App\Actions\API\V1\Player\RemoveFromClub\NotificationToRemovedPlayerAction;
use App\Models\Player;
use App\Notifications\PlayerRemovedFromClubNotification;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\API\V1\Player\RemoveFromClub\Passable as RemoveFromClubPassable;
use App\Exceptions\API\V1\ErrorSendingNotificationException;
use App\Notifications\Channels\EmailNotifier;
use Tests\Helpers\DataWithRelationsHelper;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:management')]
#[Group('api:v1:unit:management:player')]
#[Group('api:v1:unit:management:player:remove_from_club')]
#[Group('api:v1:unit:management:player:remove_from_club:actions')]
#[Group('api:v1:unit:management:player:remove_from_club:actions:notification_to_removed')]
class NotificationToRemovedPlayerActionTest extends UnitTestCase
{
    use MockeryPHPUnitIntegration;

    private NotificationToRemovedPlayerAction $action;
    private int $playerSalary = 5_000_000;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpClub();
        $this->setUpPlayer();

        $this->player = DataWithRelationsHelper::assignPlayerToClub(
            $this->club,
            playerSalary: $this->playerSalary,
        );
    }

    #[Test]
    #[Group('api:v1:unit:management:player:remove_from_club:actions:notification_to_removed:success')]
    public function it_sends_notification_to_removed_player(): void
    {
        $this->action = new NotificationToRemovedPlayerAction(
            $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
                $mock->shouldReceive('notify')
                    ->once()
                    ->with(
                        Mockery::on(fn(Player $p) => $p->is($this->player)),
                        Mockery::type(PlayerRemovedFromClubNotification::class)
                    );
            })
        );

        $passable = new RemoveFromClubPassable($this->player);
        $passable->setClub($this->club);

        $result = $this->action->handle($passable, fn($p) => $p);

        $this->assertSame($passable, $result);
    }

    #[Test]
    #[Group('api:v1:unit:management:player:remove_from_club:actions:notification_to_removed:failure')]
    public function it_throws_exception_if_notification_fails(): void
    {
        $this->action = new NotificationToRemovedPlayerAction(
            $this->setNotifierManagerWithMockedNotificationChannel('mail', new EmailNotifier(), function ($mock) {
                $mock->shouldReceive('notify')
                    ->once()
                    ->andThrow(new \RuntimeException('Fake email notification failure'));
            })
        );

        $passable = new RemoveFromClubPassable($this->player);
        $passable->setClub($this->club);

        $this->expectExceptionOnly(
            ErrorSendingNotificationException::class,
            'Failed to send notification to removed Player: Fake email notification failure',
            fn() => $this->action->handle($passable, fn($p) => $p),
        );
    }
}
