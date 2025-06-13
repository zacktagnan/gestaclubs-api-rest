<?php

namespace App\DTOs\API\V1\Player;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

readonly class WithRelationsDTO
{
    public static function from(Player $player): Player
    {
        return $player->load([
            'club' => fn($query) => $query
                ->withCount('players')
                ->with('coach'),
            // o
            // ->with(['coach' => function ($query) {
            //     $query->select('id', 'full_name', 'email');
            // }]),
        ]);
    }
}
