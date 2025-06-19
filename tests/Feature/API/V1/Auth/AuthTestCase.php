<?php

namespace Tests\Feature\API\V1\Auth;

use Tests\TestCase;

abstract class AuthTestCase extends TestCase
{
    protected string $authBaseRouteName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authBaseRouteName = 'v1.auth.';
    }
}
