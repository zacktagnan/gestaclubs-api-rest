<?php

namespace Tests\Unit\API\V1\Filters\Player;

use App\Filters\Player\EmailFilter;
use App\Models\Player;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\Traits\DataCreationForTesting;
use Tests\Helpers\Traits\PlayerDataForFilterTesting;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:filters')]
#[Group('api:v1:unit:filters:player')]
#[Group('api:v1:unit:filters:player:email')]
class EmailFilterTest extends UnitTestCase
{
    // use PlayerDataForFilterTesting;
    use DataCreationForTesting;

    #[Test]
    #[Group('api:v1:unit:filters:player:email:success')]
    public function it_filters_players_by_email()
    {
        $this->createPlayersWithVariousData([
            ['email' => 'juan@example.com'],
            ['email' => 'carla@example.com'],
            ['email' => 'pepe@example.com'],
        ]);
        // o
        // Player::factory()->create(['email' => 'juan@example.com']);
        // Player::factory()->create(['email' => 'carla@example.com']);
        // Player::factory()->create(['email' => 'pepe@example.com']);

        request()->merge(['email' => 'carla']);

        $result = (new EmailFilter())->handle(
            Player::query(),
            fn($builder) => $builder
        )->get();

        $this->assertCount(1, $result);
        $this->assertEquals('carla@example.com', $result->first()->email);
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:email:not_match')]
    public function it_returns_empty_when_email_does_not_match_any_player(): void
    {
        $this->createPlayersWithVariousData([
            ['email' => 'juan@example.com'],
            ['email' => 'carla@example.com'],
        ]);

        request()->merge(['email' => 'no-existe']);

        $result = (new EmailFilter())->handle(
            Player::query(),
            fn($builder) => $builder
        )->get();

        $this->assertCount(0, $result);
    }
}
