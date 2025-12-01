<?php
declare(strict_types=1);

namespace App\Domain\Session\Repositories;

interface DbSessionStorageInterface extends SessionStorageInterface
{
    // additional db session specific methods
    public function findActiveSessionsByUser(string $userId): array;
    public function revokeSessionsByUser(string $userId, ?string $revocationReason = null): int;
    public function updateMetadata(string $sessionId, array $meta): bool;
}
