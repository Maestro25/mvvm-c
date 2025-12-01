<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Shared\ValueObjects\SessionId;

interface SessionTokenManagerInterface
{
    /**
     * Create new tokens for a given session id.
     *
     * @param SessionId $sessionId
     * @return SessionInterface Updated session entity.
     * @throws \Throwable
     */
    public function createTokensForSession(SessionId $sessionId): SessionInterface;

    /**
     * Validate an access token and return session entity or null.
     *
     * @param string $token
     * @return SessionInterface|null
     */
    public function validateAccessToken(string $token): ?SessionInterface;

    /**
     * Validate a refresh token and return session entity or null.
     *
     * @param string $token
     * @return SessionInterface|null
     */
    public function validateRefreshToken(string $token): ?SessionInterface;

    /**
     * Revoke a session by id.
     *
     * @param SessionId $sessionId
     * @param string|null $actorId
     * @param string|null $reason
     * @return bool Success status
     */
    public function revokeSession(SessionId $sessionId, ?string $actorId = null, ?string $reason = null): bool;
}
