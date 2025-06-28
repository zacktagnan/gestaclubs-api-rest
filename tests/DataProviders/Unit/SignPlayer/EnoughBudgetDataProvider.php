<?php

namespace Tests\DataProviders\Unit\SignPlayer;

class EnoughBudgetDataProvider
{
    public static function provideSufficientBudgetScenarios(): array
    {
        return [
            'budget sufficient (no players)' => [
                'budget' => 10_000_000,
                'coachSalary' => 5_000_000,
                'playerSalaries' => [],
                'playerSalary' => 3_000_000,
            ],
            'budget sufficient (players + coach)' => [
                'budget' => 15_000_000,
                'coachSalary' => 4_000_000,
                'playerSalaries' => [3_000_000, 2_000_000],
                'playerSalary' => 5_000_000,
            ],
            'budget exact match' => [
                'budget' => 11_000_000,
                'coachSalary' => 4_000_000,
                'playerSalaries' => [2_000_000, 2_000_000],
                'playerSalary' => 3_000_000,
            ],
        ];
    }

    public static function provideExceededBudgetScenarios(): array
    {
        return [
            'budget exceeded' => [
                'budget' => 11_000_000,
                'coachSalary' => 3_000_000,
                'playerSalaries' => [3_000_000, 3_000_000],
                'playerSalary' => 4_000_000,
            ],
        ];
    }
}
