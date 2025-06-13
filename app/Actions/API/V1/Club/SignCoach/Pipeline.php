<?php

namespace App\Actions\API\V1\Club\SignCoach;

use Illuminate\Pipeline\Pipeline as IlluminatePipeline;

final class Pipeline
{
    public static function execute(array $data): Passable
    {
        $passable = app(
            Passable::class,
            [
                'data' => $data,
            ]
        );

        return app(IlluminatePipeline::class)
            ->send($passable)
            ->through([
                EnsureClubHasNoCoachAssignedAction::class,
                EnsureCoachIsNotAlreadyAssignedAction::class,
                EnsureClubHasEnoughBudgetAction::class,
                AssignCoachToClubAction::class,
                NotificationToAssignedCoachAction::class,
            ])
            ->thenReturn();
    }
}
