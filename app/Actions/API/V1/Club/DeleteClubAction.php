<?php

namespace App\Actions\API\V1\Club;

use App\Exceptions\API\V1\ClubHasMembersException;
use App\Models\Club;

final class DeleteClubAction
{
    public function execute(Club $club): void
    {
        if ($club->players()->exists() || $club->coach) {
            throw new ClubHasMembersException(
                'This Club still has Players or a Coach assigned, so it cannot be deleted.'
            );
        }

        $club->delete();
    }
}
