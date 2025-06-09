<?php

namespace App\DTOs\API\V1\Club\SignPlayer;

use App\Http\Requests\API\V1\Club\ClubSignPlayerRequest;

readonly class AssignPlayerToClubDTO
{
    private function __construct(public int $playerId, public int $salary) {}

    public static function fromRequest(ClubSignPlayerRequest $request): self
    {
        return new self(
            playerId: $request->validated('player_id'),
            salary: $request->validated('salary'),
        );
    }

    public function toArray(): array
    {
        return [
            'player_id' => $this->playerId,
            'salary' => $this->salary,
        ];
    }
}
