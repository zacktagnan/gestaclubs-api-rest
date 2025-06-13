<?php

namespace App\Actions\API\V1\Coach\RemoveFromClub;

use App\Models\Club;
use App\Models\Coach;

final class Passable
{
    private Club $club;

    public function __construct(private readonly Coach $coach) {}

    public function getCoach(): Coach
    {
        return $this->coach;
    }

    public function setCoach(Coach $coach): void
    {
        $this->coach = $coach;
    }

    public function getClub(): Club
    {
        return $this->club;
    }

    public function setClub(Club $club): void
    {
        $this->club = $club;
    }
}
