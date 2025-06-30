<?php

namespace Tests\Feature\API\V1\Auth\LogoutController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\API\V1\Auth\AuthTestCase;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:auth')]
#[Group('api:v1:feat:auth:logout')]
class LogoutTest extends AuthTestCase
{
    #[Test]
    #[Group('api:v1:feat:auth:logout:success')]
    public function an_user_can_logout(): void
    {
        $this
            ->withToken($this->token)
            ->postJson(route($this->authBaseRouteName . 'logout'))
            ->assertOk();

        // Comprobando que el token se ha eliminado correctamente en memoria
        $this->assertEmpty($this->user->tokens);

        // Comprobando que el token se ha eliminado correctamente en la base de datos
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
            'tokenable_type' => get_class($this->user),
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:auth:logout:unauthenticated')]
    public function an_unauthenticated_user_cannot_logout(): void
    {
        $this
            ->postJson(route($this->authBaseRouteName . 'logout'))
            ->assertUnauthorized();
    }
}
