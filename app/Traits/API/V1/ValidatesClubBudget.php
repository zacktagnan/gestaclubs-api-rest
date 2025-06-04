<?php

namespace App\Traits\API\V1;

trait ValidatesClubBudget
{
    protected function validateBudgetSufficiency($validator)
    {
        $validator->after(function ($validator) {
            $club = $this->route('club');

            $coachSalary = $club->coach?->salary ?? 0;
            $playersSalary = $club->players()->sum('salary');
            $requiredBudget = $coachSalary + $playersSalary;

            if ($this->budget < $requiredBudget) {
                $validator->errors()->add(
                    'budget',
                    "The budget must be at least {$requiredBudget} to cover salaries."
                );
            }
        });
    }
}
