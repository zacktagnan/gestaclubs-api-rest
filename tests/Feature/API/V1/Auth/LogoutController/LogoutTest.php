<?php

namespace Tests\Feature\API\V1\Auth\LogoutController;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\API\V1\Auth\AuthTestCase;

#[Group('api:v1')]
#[Group('api:v1:auth')]
#[Group('api:v1:auth:logout')]
class LogoutTest extends AuthTestCase
{
    #[Test]
    #[Group('api:v1:auth:logout:success')]
    public function an_user_can_logout(): void
    {
        $this
            ->withToken($this->token)
            ->postJson(route($this->authBaseRouteName . 'logout'))
            ->assertOk();

        $this->assertEmpty($this->user->tokens);
    }

    #[Test]
    #[Group('api:v1:auth:logout:unauthenticated')]
    public function an_unauthenticated_user_cannot_logout(): void
    {
        $this
            ->postJson(route($this->authBaseRouteName . 'logout'))
            ->assertUnauthorized();
    }
}
