<?php
declare(strict_types=1);

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Entities\PasswordHistory;
use App\Domain\ValueObjects\UserId;

interface PasswordHistoryRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieves recent password history entries for a user, limited by count.
     *
     * @param UserId $userId
     * @param int $limit Number of recent entries to retrieve
     * @return PasswordHistory[]
     */
    public function findRecentByUserId(UserId $userId, int $limit): array;
    /**
     * Factory method to create a new PasswordHistory entity.
     *
     * @param UserId $userId
     * @param \App\Domain\ValueObjects\PasswordHash $passwordHash
     * @return PasswordHistory
     */
    public function createEntry(UserId $userId, \App\Domain\ValueObjects\PasswordHash $passwordHash): PasswordHistory;
}
