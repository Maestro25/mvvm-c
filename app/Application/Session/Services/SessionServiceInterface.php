<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Application\Session\Validation\Exceptions\SessionInvalidException;

interface SessionServiceInterface
{
    /**
     * Start or resume a PHP session.
     *
     * @throws \Throwable
     */
    public function startSession(): void;

    /**
     * Check if the user is authenticated.
     */
    public function isUserAuthenticated(): bool;

    /**
     * Validate an access token and return associated session or null.
     *
     * @param string $token
     * @return SessionInterface|null
     * @throws SessionInvalidException
     */
    public function validateAccessToken(string $token): ?SessionInterface;

    /**
     * Destroy the current session.
     *
     * @throws \Throwable
     */
    public function destroySession(): void;

    /**
     * Renew session tokens and return the updated session.
     *
     * @param SessionId $sessionId
     * @return SessionInterface
     * @throws SessionInvalidException
     */
    public function renewSessionTokens(SessionId $sessionId): SessionInterface;

    /**
     * Update session metadata.
     *
     * @param SessionId $sessionId
     * @param array $meta
     * @return bool
     */
    public function updateSessionMeta(SessionId $sessionId, array $meta): bool;
}
