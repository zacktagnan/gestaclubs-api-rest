<?php

namespace App\Traits\API\V1;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Trait HasClub
 *
 * Se utiliza, únicamente, en seeders y factories para generar datos
 * aleatorios con o sin relación con un club. No se usa en producción.
 */
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
