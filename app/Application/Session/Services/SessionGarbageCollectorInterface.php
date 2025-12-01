<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

interface SessionGarbageCollectorInterface
{
    /**
     * Cleans up expired and revoked sessions.
     *
     * @return int Number of sessions deleted.
     */
    public function collectGarbage(): int;
}
