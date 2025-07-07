<?php

namespace Tests\Unit\API\V1\Filters\Player;

use App\Filters\Player\FullNameFilter;
use App\Models\Player;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\Traits\DataCreationForTesting;
use Tests\Unit\API\V1\UnitTestCase;

#[Group('api:v1')]
#[Group('api:v1:unit')]
#[Group('api:v1:unit:filters')]
#[Group('api:v1:unit:filters:player')]
#[Group('api:v1:unit:filters:player:full_name')]
class FullNameFilterTest extends UnitTestCase
{
    use DataCreationForTesting;

    #[Test]
    #[Group('api:v1:unit:filters:player:full_name:success')]
    public function it_applies_full_name_filter(): void
    {
        $this->createPlayersOnlyWithFullName([
            'Juan Carlos',
            'Carlos García',
            'Pepe Rodríguez',
        ]);

        // Simular la query string
        request()->merge(['full_name' => 'Carlos']);

        $builder = Player::query();
        // IMPORTANTE: Sin los () envolviendo la instancia, la extensión "PHPUnit Test Explorer" no se aplica
        $filtered = (new FullNameFilter())->handle($builder, fn($b) => $b)->get();

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->pluck('full_name')->contains('Juan Carlos'));
        $this->assertTrue($filtered->pluck('full_name')->contains('Carlos García'));
    }

    #[Test]
    #[Group('api:v1:unit:filters:player:full_name:missing')]
    public function it_skips_filter_if_full_name_is_missing(): void
    {
        Player::factory()->count(2)->create();

        // No se pasa full_name en la query
        request()->replace([]);

        $result = (new FullNameFilter())->handle(Player::query(), fn($b) => $b)->get();

        $this->assertCount(2, $result);
    }
}
