<?php

namespace Tests\DataProviders;

class PlayerDataProvider
{
    public static function providePlayerDataToCreate(): array
    {
        return [
            'player_data_to_create' => [
                [
                    'full_name' => 'Pitxitxi Naiz',
                    'email' => 'pitxitxi.naiz@kirolaria.eus',
                ],
            ],
        ];
    }

    public static function provideInvalidPlayerData(): array
    {
        return [
            'empty payload' => [
                [],
                ['full_name', 'email'],
            ],
            'missing full name' => [
                [
                    'email' => 'pitxitxi.naiz@kirolaria.eus',
                ],
                ['full_name'],
            ],
            'missing email' => [
                [
                    'full_name' => 'Pitxitxi Naiz',
                ],
                ['email'],
            ],
        ];
    }
}
