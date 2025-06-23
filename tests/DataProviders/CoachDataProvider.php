<?php

namespace Tests\DataProviders;

class CoachDataProvider
{
    public static function provideCoachDataToCreate(): array
    {
        return [
            'coach_data_to_create' => [
                [
                    'full_name' => 'Kepa Arrizabalaga',
                    'email' => 'kepa.arrizabalaga@euskaltel.eus',
                ],
            ],
        ];
    }

    public static function provideInvalidCoachData(): array
    {
        return [
            'empty payload' => [
                [],
                ['full_name', 'email'],
            ],
            'missing full name' => [
                [
                    'email' => 'kepa.arrizabalaga@euskaltel.eus',
                ],
                ['full_name'],
            ],
            'missing email' => [
                [
                    'full_name' => 'Kepa Arrizabalaga',
                ],
                ['email'],
            ],
        ];
    }
}
