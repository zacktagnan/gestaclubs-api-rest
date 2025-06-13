<?php

namespace App\Actions\API\V1\Player\RemoveFromClub;

use App\Models\Player;
use Illuminate\Pipeline\Pipeline as IlluminatePipeline;

final class Pipeline
{
    public static function execute(Player $player): Passable
    {
        $passable = app(
            Passable::class,
            [
                'player' => $player,
            ]
        );

        return app(IlluminatePipeline::class)
            ->send($passable)
            ->through([
                RemoveFromClubAction::class,
                NotificationToRemovedPlayerAction::class,
            ])
            ->thenReturn();
    }
}
