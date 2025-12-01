<?php
declare(strict_types=1);

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Entities\EmailVerificationToken;
use App\Domain\ValueObjects\UserId;
use App\Domain\ValueObjects\VerificationToken;

interface EmailVerificationTokenRepositoryInterface extends RepositoryInterface
{
    /**
     * Finds an email verification token entity by token value (VO).
     * Returns null if not found or expired/used.
     */
    public function findByToken(VerificationToken $token): ?EmailVerificationToken;

    /**
     * Finds the latest valid token for the user.
     */
    public function findByUserId(UserId $userId): ?EmailVerificationToken;
    /**
     * Invalidate a token by marking used_at timestamp.
     */
    public function invalidate(string $token): bool;

    /**
     * Delete all tokens for a given user.
     */
    public function deleteTokensForUser(UserId $userId): bool;
}
