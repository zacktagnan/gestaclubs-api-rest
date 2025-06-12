<?php

namespace App\Models\Contracts;

interface NotifiableEntityInterface
{
    /**
     * @return array<int|string>
     */
    public function preferredNotificationChannels(): array;
}
