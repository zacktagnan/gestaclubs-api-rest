<?php

namespace Tests\DataProviders;

class AuthDataProvider
{
    public static function provideUserDataToRegister(): array
    {
        return [
            'user_data_to_register' => [
                [
                    'name' => 'Test User',
                    'email' => 'register@test.es',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                    'device_name' => 'testing',
                ],
            ],
        ];
    }

    public static function provideInvalidRegistrationData(): array
    {
        return [
            'empty payload' => [
                [],
                ['name', 'email', 'password', 'device_name'],
            ],
            'missing email' => [
                [
                    'name' => 'User',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                    'device_name' => 'testing',
                ],
                ['email'],
            ],
            'passwords do not match' => [
                [
                    'name' => 'User',
                    'email' => 'user@test.com',
                    'password' => 'password',
                    'password_confirmation' => 'wrong',
                    'device_name' => 'testing',
                ],
                ['password'],
            ],
            'missing device_name' => [
                [
                    'email' => 'user@test.com',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ],
                ['device_name'],
            ],
        ];
    }

    public static function provideUserBaseDataToLogin(): array
    {
        return [
            'user_base_data_to_login' => [
                [
                    'password' => 'password',
                    'device_name' => 'testing',
                ]
            ],
        ];
    }

    public static function provideInvalidLoginData(): array
    {
        return [
            'empty payload' => [
                [],
                ['email', 'password', 'device_name'],
            ],
            'missing email' => [
                [
                    'password' => 'password',
                    'device_name' => 'testing',
                ],
                ['email'],
            ],
            'password do not match' => [
                [
                    'email' => 'user@test.com',
                    'password' => 'wrong',
                    'device_name' => 'testing',
                ],
                ['password'],
            ],
            'missing device_name' => [
                [
                    'email' => 'user@test.com',
                    'password' => 'password',
                ],
                ['device_name'],
            ],
        ];
    }
}
