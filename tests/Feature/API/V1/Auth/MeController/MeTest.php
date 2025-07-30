<?php

namespace Tests\Feature\API\V1\Auth\MeController;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\API\V1\Auth\AuthTestCase;

#[Group('api:v1')]
#[Group('api:v1:feat')]
#[Group('api:v1:feat:auth')]
#[Group('api:v1:feat:auth:me')]
class MeTest extends AuthTestCase
{
    #[Test]
    #[Group('api:v1:feat:auth:me:success')]
    public function user_can_retrieve_their_authenticated_information(): void
    {
        $response = $this
            ->withToken($this->token)
            ->getJson(route($this->authBaseRouteName . 'me'));

        // Verificar que la respuesta es exitosa
        $response->assertOk();

        // Verificar que contiene la clave "user"
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
    }

    #[Test]
    #[Group('api:v1:feat:auth:me:unauthenticated')]
    public function user_cannot_access_me_endpoint_without_token(): void
    {
        $response = $this
            ->getJson(route($this->authBaseRouteName . 'me'))
            ->assertUnauthorized();

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
