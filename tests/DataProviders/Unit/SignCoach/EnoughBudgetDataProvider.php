<?php

namespace Tests\DataProviders\Unit\SignCoach;

class EnoughBudgetDataProvider
{
    public static function provideSufficientBudgetScenarios(): array
    {
        return [
            'budget sufficient (no players)' => [
                'budget' => 10_000_000,
                'playerSalaries' => [],
                'coachSalary' => 5_000_000,
            ],
            'budget sufficient (players + coach)' => [
                'budget' => 15_000_000,
                'playerSalaries' => [5_000_000, 4_000_000],
                'coachSalary' => 5_000_000,
            ],
            'budget exact match' => [
                'budget' => 12_000_000,
                'playerSalaries' => [4_000_000, 4_000_000],
                'coachSalary' => 4_000_000,
            ],
        ];
    }

    public static function provideExceededBudgetScenarios(): array
    {
        return [
            'budget exceeded' => [
                'budget' => 10_000_000,
                'playerSalaries' => [4_000_000, 4_000_000],
                'coachSalary' => 4_000_000,
            ],
        ];
    }
}
