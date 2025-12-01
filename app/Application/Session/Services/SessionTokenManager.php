<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Application\Shared\Services\UserContextInterface;
use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Shared\ValueObjects\IpAddress;
use Psr\Log\LoggerInterface;
use App\Domain\Session\Repositories\SessionRepositoryInterface;
use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Session\Factories\SessionFactoryInterface;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\GuestId;
use RuntimeException;

final class SessionTokenManager implements SessionTokenManagerInterface
{
    private SessionFactoryInterface $sessionFactory;

    public function __construct(
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly SessionRepositoryInterface $sessionRepository,
        private readonly LoggerInterface $logger,
        private readonly UserContextInterface $userContext,
        SessionFactoryInterface $sessionFactory,
    ) {
        $this->sessionFactory = $sessionFactory;
    }

    public function createTokensForSession(SessionId $sessionId): SessionInterface
    {
        $context = $this->additionalLogContext() + ['session_id' => (string) $sessionId];
        $isGuest = $this->userContext->getUserId() instanceof GuestId;

        try {
            $this->logger->debug('Creating new tokens for session', $context);

            $now = new \DateTimeImmutable();
            $accessTokenExpiry = new ExpirationTime($now->modify('+1 hour'));
            $refreshTokenExpiry = new ExpirationTime($now->modify('+30 days'));
            $csrfTokenExpiry = new ExpirationTime($now->modify('+1 hour'));

            $accessToken = $this->tokenGenerator->generateSessionToken($accessTokenExpiry);
            $refreshToken = $this->tokenGenerator->generateRefreshToken($refreshTokenExpiry);
            $csrfToken = $this->tokenGenerator->generateCsrfToken($csrfTokenExpiry);

            $this->logger->debug('Generated raw access token', ['token' => $accessToken]);
            $this->logger->debug('Generated raw refresh token', ['token' => $refreshToken]);
            $this->logger->debug('Generated raw CSRF token', ['token' => $csrfToken]);

            if ($isGuest) {
                $session = $this->sessionFactory->createTransientSession(
                    $sessionId,
                    $accessToken,
                    $refreshToken,
                    $csrfToken,
                    $accessTokenExpiry,
                    new IpAddress('0.0.0.0'),
                    null,
                    SessionStatus::ACTIVE,
                    null
                );
            } else {
                $session = $this->sessionRepository->getById($sessionId);
                if ($session === null) {
                    $this->logger->error('Session not found for token creation', $context);
                    throw new RuntimeException('Session not found for ID ' . (string) $sessionId);
                }
                $session->updateTokens($accessToken, $refreshToken, $csrfToken, $accessTokenExpiry);
            }

            if (method_exists($session, 'setLogger')) {
                $session->setLogger($this->logger);
            }

            if (method_exists($session, 'validate')) {
                $session->validate();
            }

            if (!$isGuest) {
                $this->sessionRepository->save($session);
            }

            $this->logger->info('Created new tokens for session', $context);

            return $session;
        } catch (\Throwable $e) {
            $this->logger->error('Error creating tokens for session', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }



    public function validateAccessToken(string $token): ?SessionInterface
    {
        $context = $this->additionalLogContext() + ['token' => $token];
        try {
            $this->logger->debug('Validating access token', $context);

            $session = $this->sessionRepository->getByAccessToken($token);

            if (!$session || !$session->isActive()) {
                $this->logger->notice('Access token validation failed: inactive or missing session', $context);
                return null;
            }

            if ($session->isExpired() || $session->getAccessToken()->getToken() !== $token) {
                $this->logger->notice('Access token validation failed: expired or mismatch', $context);
                return null;
            }

            $this->logger->info('Access token validated', array_merge($context, ['session_id' => (string) $session->getId()]));
            return $session;
        } catch (\Throwable $e) {
            $this->logger->error('Exception during access token validation', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    public function validateRefreshToken(string $token): ?SessionInterface
    {
        $context = $this->additionalLogContext() + ['token' => $token];
        try {
            $this->logger->debug('Validating refresh token', $context);

            $session = $this->sessionRepository->getByRefreshToken($token);

            if (!$session || !$session->isActive()) {
                $this->logger->notice('Refresh token validation failed: inactive or missing session', $context);
                return null;
            }

            $refreshToken = $session->getRefreshToken();
            if ($refreshToken === null || $refreshToken->getToken() !== $token || $refreshToken->isExpired()) {
                $this->logger->notice('Refresh token validation failed: token mismatch or expired', $context);
                return null;
            }

            $this->logger->info('Refresh token validated', array_merge($context, ['session_id' => (string) $session->getId()]));
            return $session;
        } catch (\Throwable $e) {
            $this->logger->error('Exception during refresh token validation', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    public function revokeSession(SessionId $sessionId, ?string $actorId = null, ?string $reason = null): bool
    {
        $context = $this->additionalLogContext() + [
            'session_id' => (string) $sessionId,
            'actor_id' => $actorId,
            'reason' => $reason,
        ];
        try {
            $this->logger->debug('Revoking session', $context);

            $result = $this->sessionRepository->revokeSession($sessionId, $actorId, $reason);

            if ($result) {
                $this->logger->info('Session revoked', $context);
            } else {
                $this->logger->warning('Failed to revoke session', ['session_id' => (string) $sessionId]);
            }

            return $result;
        } catch (\Throwable $e) {
            $this->logger->error('Error revoking session', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    /**
     * Return user context info for logging.
     *
     * @return array<string, string|null>
     */
    private function additionalLogContext(): array
    {
        return [
            'user_id' => $this->userContext->getUserId(),
            'guest_id' => $this->userContext->getGuestId(),
            'session_id' => session_id() ?: null,
        ];
    }
}
