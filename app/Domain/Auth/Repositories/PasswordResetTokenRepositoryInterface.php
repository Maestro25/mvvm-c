<?php
declare(strict_types=1);

namespace App\Domain\Repositories\Interfaces;

use App\Domain\ValueObjects\UserId;
use App\Domain\Entities\PasswordResetToken;
use DateTimeImmutable;

interface PasswordResetTokenRepositoryInterface extends RepositoryInterface
{
    /**
     * Find associated UserId by reset token hash if valid and not expired.
     *
     * @param string $tokenHash
     * @return UserId|null
     */
    public function findUserIdByTokenHash(string $tokenHash): ?UserId;

    public function findTokenByHash(string $tokenHash): ?PasswordResetToken;
    
    /**
     * Invalidate a reset token by marking it as used.
     *
     * @param string $tokenHash
     * @return bool True if invalidation succeeded
     */
    public function invalidate(string $tokenHash): bool;
    public function findByUserId(UserId $userId): array;
    /**
     * Delete all password reset tokens for a given user.
     *
     * @param UserId $userId
     * @return bool True if deletion succeeded
     */
    public function deleteTokensForUser(UserId $userId): bool;

    // Test
    public function getAllTokensWithExpiry(): array;
}
