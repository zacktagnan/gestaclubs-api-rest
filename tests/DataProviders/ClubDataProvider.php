<?php

namespace Tests\DataProviders;

class ClubDataProvider
{
    public static function provideClubDataToCreate(): array
    {
        return [
            'club_data_to_create' => [
                [
                    'name' => 'Test Club',
                    'budget' => 9600000,
                ],
            ],
        ];
    }

    public static function provideInvalidClubData(): array
    {
        return [
            'empty payload' => [
                [],
                ['name', 'budget'],
            ],
            'missing name' => [
                [
                    'budget' => 9600000,
                ],
                ['name'],
            ],
            'missing budget' => [
                [
                    'name' => 'Test Club',
                ],
                ['budget'],
            ],
            'minimum budget not met' => [
                [
                    'name' => 'Test Club',
                    'budget' => 0,
                ],
                ['budget'],
            ],
        ];
    }

    public static function provideClubDataToUpdate(): array
    {
        return [
            'club_data_to_update' => [
                [
                    'name' => 'Updated Club Name',
                    'budget' => 432000,
                ],
            ],
        ];
    }
}
