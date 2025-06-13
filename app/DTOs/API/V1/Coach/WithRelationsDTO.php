<?php

namespace App\DTOs\API\V1\Coach;

use App\Models\Coach;

readonly class WithRelationsDTO
{
    public static function from(Coach $coach): Coach
    {
        return $coach->load([
            'club' => fn($query) => $query
                ->withCount('players'),
        ]);
    }
}
