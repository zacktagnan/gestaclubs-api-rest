<?php

namespace App\Traits\API\V1;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

trait HasClub
{
    public function withoutClub(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'club_id' => null,
        ]);
    }

    public function withClub(Club $club): Factory
    {
        return $this->state(fn(array $attributes) => [
            'club_id' => $club->id,
        ]);
    }
}
