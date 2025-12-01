<?php
declare(strict_types=1);

namespace App\Domain\Session\Repositories;

use App\Domain\Session\Entities\Session;
use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\Exceptions\EntityNotFoundException;

interface SessionRepositoryInterface extends RepositoryInterface
{
    public function getById(IdentityInterface $id): SessionInterface;

    public function getByAccessToken(string $accessToken): ?SessionInterface;

    public function getByRefreshToken(string $refreshToken): ?SessionInterface;

    /**
     * Get all active (non-revoked, non-expired) sessions for a user.
     * @return EntityInterface[]
     */
    public function getActiveSessionsByUser(IdentityInterface $userId): array;

    public function save(EntityInterface $entity): bool;

    public function delete(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $ipBinary = null): bool;

    public function revokeSession(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $reason = null): bool;

    /**
     * Cleanup expired sessions, returning count of deleted rows
     */
    public function deleteExpiredSessions(): int;

    public function updateLastUsed(IdentityInterface $id, \DateTimeImmutable $lastUsedAt): bool;

    /**
     * Search sessions by multiple criteria with pagination
     * @return EntityInterface[]
     */
    public function searchSessions(array $criteria, int $limit, int $offset): array;
    public function updateMetadata(IdentityInterface $id, array $meta): bool;
}
