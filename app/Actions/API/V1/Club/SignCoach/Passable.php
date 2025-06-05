<?php

namespace App\Actions\API\V1\Club\SignCoach;

use App\Models\Coach;

final class Passable
{
    private Coach $coach;

    public function __construct(private readonly array $data) {}

    public function getPropertyFromData(string $property): mixed
    {
        return data_get($this->data, $property);
    }

    public function getCoach(): Coach
    {
        return $this->coach;
    }

    public function setCoach(Coach $coach): void
    {
        $this->coach = $coach;
    }
}
