<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use Psr\Log\LoggerInterface;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Shared\ValueObjects\ExpirationTime;

final class TokenGenerator implements TokenGeneratorInterface
{
    private const DEFAULT_ACCESS_TOKEN_LENGTH = 32; // generates 64 hex chars
    private const DEFAULT_REFRESH_TOKEN_LENGTH = 64; // generates 128 hex chars
    private const DEFAULT_CSRF_TOKEN_LENGTH = 16; // generates 32 hex chars


    private readonly LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private readonly int $accessTokenLength = self::DEFAULT_ACCESS_TOKEN_LENGTH,
        private readonly int $refreshTokenLength = self::DEFAULT_REFRESH_TOKEN_LENGTH,
        private readonly int $csrfTokenLength = self::DEFAULT_CSRF_TOKEN_LENGTH
    ) {
        $this->logger = $logger;
    }

    /**
     * Generate a cryptographically secure raw token of specified length.
     *
     * @param int $length
     * @return string
     * @throws \InvalidArgumentException|\Throwable
     */
    private function generateRawToken(int $length): string
    {
        if ($length <= 0) {
            $this->logger->error('Invalid token length requested', ['length' => $length]);
            throw new \InvalidArgumentException('Token length must be positive');
        }

        try {
            $token = bin2hex(random_bytes($length));
            $this->logger->debug('Generated raw token', ['length' => $length, 'token' => $token]);
            return $token;
        } catch (\Throwable $e) {
            $this->logger->error('Error generating raw token', [
                'exception' => $e,
                'length' => $length,
            ]);
            throw $e;
        }
    }

    /**
     * Generate a SessionToken with expiration.
     *
     * @param ExpirationTime $expiry
     * @return SessionToken
     */
    public function generateSessionToken(ExpirationTime $expiry): SessionToken
    {
        $tokenStr = $this->generateRawToken($this->accessTokenLength);
        $this->logger->info('Generated new session token', ['token' => $tokenStr]);
        return new SessionToken($tokenStr, $expiry);
    }

    /**
     * Generate a RefreshToken with expiration.
     *
     * @param ExpirationTime $expiry
     * @return RefreshToken
     */
    public function generateRefreshToken(ExpirationTime $expiry): RefreshToken
    {
        $tokenStr = $this->generateRawToken($this->refreshTokenLength);
        $this->logger->info('Generated new refresh token', ['token' => $tokenStr]);
        return new RefreshToken($tokenStr, $expiry);
    }

    /**
     * Generate a CsrfToken with expiration.
     *
     * @param ExpirationTime $expiry
     * @return CsrfToken
     */
    public function generateCsrfToken(ExpirationTime $expiry): CsrfToken
    {
        $tokenStr = $this->generateRawToken($this->csrfTokenLength);
        $this->logger->info('Generated new CSRF token', ['token' => $tokenStr]);
        return new CsrfToken($tokenStr, $expiry);
    }
}
