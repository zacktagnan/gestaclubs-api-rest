<?php

namespace App\Actions\API\V1\Coach\RemoveFromClub;

use App\Models\Coach;
use Illuminate\Pipeline\Pipeline as IlluminatePipeline;

final class Pipeline
{
    public static function execute(Coach $coach): Passable
    {
        $passable = app(
            Passable::class,
            [
                'coach' => $coach,
            ]
        );

        return app(IlluminatePipeline::class)
            ->send($passable)
            ->through([
                RemoveFromClubAction::class,
                NotificationToRemovedCoachAction::class,
            ])
            ->thenReturn();
    }
}
