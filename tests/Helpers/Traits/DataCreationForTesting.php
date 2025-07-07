<?php

namespace Tests\Helpers\Traits;

use App\Models\Club;
use App\Models\Coach;
use App\Models\Player;
use Tests\Helpers\DataWithRelationsHelper;

trait DataCreationForTesting
{
    protected function createClub(): Club
    {
        return Club::factory()->create();
    }

    protected function createClubWithStaff(int $coachSalary = 4_000_000, array $playerSalaries = []): Club
    {
        $club = $this->createClub();
        DataWithRelationsHelper::assignStaffToClub($club, $coachSalary, $playerSalaries);

        return $club;
    }

    protected function createCoach(): Coach
    {
        return Coach::factory()->create();
    }

    protected function createPlayer(): Player
    {
        return Player::factory()->create();
    }

    protected function createCoachAssignedToClub(Club $club, int $coachSalary = 4_000_000): Coach
    {
        return DataWithRelationsHelper::assignCoachToClub($club, $coachSalary);
    }

    protected function createPlayerAssignedToClub(Club $club, int $playerSalary = 7_000_000): Player
    {
        return DataWithRelationsHelper::assignPlayerToClub($club, $playerSalary);
    }

    /**
     * Create little Player list using a full_name array.
     *
     * @param array<int, string> $fullNames
     */
    public function createPlayersOnlyWithFullName(array $fullNames = ['Juan Carlos', 'Carlos García', 'Pepe Rodríguez']): void
    {
        foreach ($fullNames as $fullName) {
            Player::factory()->create(['full_name' => $fullName]);
        }
    }

    /**
     * Crea múltiples jugadores con posibilidad de definir atributos globales o individuales.
     *
     * @param  array<int, string|array<string, mixed>> $players
     *        Ej: ['Juan', ['full_name' => 'Ana', 'email' => 'ana@mail.com']]
     * @param  array<string, mixed> $globalOverrides
     *        Atributos comunes a todos los jugadores.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createPlayersWithVariousData(array $players, array $globalOverrides = [])
    {
        return collect($players)->map(function ($item, $index) use ($globalOverrides) {
            if (is_string($item)) {
                $attributes = ['full_name' => $item];
            } elseif (is_array($item)) {
                $attributes = $item;
            } else {
                throw new \InvalidArgumentException('Cada jugador debe generarse mediante un string de "full_name" o un array de atributos.');
            }

            // Si hay email global, hacerlo único
            if (isset($globalOverrides['email'])) {
                $email = $globalOverrides['email'];
                $emailParts = explode('@', $email);
                $uniqueEmail = $emailParts[0] . "-$index@" . $emailParts[1];

                $globalOverrides['email'] = $uniqueEmail;
            }

            return Player::factory()->create(array_merge($attributes, $globalOverrides));
        });
    }
}
