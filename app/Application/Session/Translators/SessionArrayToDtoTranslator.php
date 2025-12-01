<?php
declare(strict_types=1);

namespace App\Application\Session\Translators;


use App\Application\Session\DTOs\SessionCreationData;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Session\Enums\SessionStatus;
use Psr\Log\LoggerInterface;

final class SessionArrayToDtoTranslator
{
    private const ACCESS_TOKEN_EXPIRY_SECONDS = 900; // 15 minutes
    private const REFRESH_TOKEN_EXPIRY_SECONDS = 1209600; // 14 days
    private const CSRF_TOKEN_EXPIRY_SECONDS = 43200; // 12 hours
    public function __construct(
        private readonly LoggerInterface $logger
    ) {

    }
    /**
     * Translates raw session array to the SessionCreationData DTO.
     *
     * @param array $sessionData Raw session associative array.
     * @return SessionCreationData Fully typed DTO for session creation.
     */
    public function toDto(array $sessionData): SessionCreationData
    {
        $now = new \DateTimeImmutable();

        $this->logger->debug('Translating session data to DTO', [
            'access_token_raw' => $sessionData['access_token'] ?? null,
            'refresh_token_raw' => $sessionData['refresh_token'] ?? null,
            'csrf_token_raw' => $sessionData['csrf_token'] ?? null,
        ]);

        $accessTokenExpiry = isset($sessionData['access_token_expires_at'])
            ? new ExpirationTime(new \DateTimeImmutable($sessionData['access_token_expires_at']))
            : new ExpirationTime($now->add(new \DateInterval('PT' . self::ACCESS_TOKEN_EXPIRY_SECONDS . 'S')));

        $refreshTokenExpiry = isset($sessionData['refresh_token_expires_at'])
            ? new ExpirationTime(new \DateTimeImmutable($sessionData['refresh_token_expires_at']))
            : new ExpirationTime($now->add(new \DateInterval('PT' . self::REFRESH_TOKEN_EXPIRY_SECONDS . 'S')));

        $csrfTokenExpiry = isset($sessionData['csrf_token_expires_at'])
            ? new ExpirationTime(new \DateTimeImmutable($sessionData['csrf_token_expires_at']))
            : new ExpirationTime($now->add(new \DateInterval('PT' . self::CSRF_TOKEN_EXPIRY_SECONDS . 'S')));

        $sessionId = isset($sessionData['session_id'])
            ? new SessionId($sessionData['session_id'])
            : throw new \InvalidArgumentException('Missing session_id');

        $userId = isset($sessionData['user_id'])
            ? UserId::fromString($sessionData['user_id'])
            : throw new \InvalidArgumentException('Missing user_id');

        $accessToken = isset($sessionData['access_token'])
            ? new SessionToken($sessionData['access_token'], $accessTokenExpiry)
            : throw new \InvalidArgumentException('Missing access_token');

        $refreshToken = isset($sessionData['refresh_token'])
            ? new RefreshToken($sessionData['refresh_token'], $refreshTokenExpiry)
            : null;

        $csrfToken = isset($sessionData['csrf_token'])
            ? new CsrfToken($sessionData['csrf_token'], $csrfTokenExpiry)
            : null;

        $createdIp = isset($sessionData['created_ip'])
            ? new IpAddress($sessionData['created_ip'])
            : throw new \InvalidArgumentException('Missing created_ip');

        $lastIpAddress = isset($sessionData['last_ip_address'])
            ? new IpAddress($sessionData['last_ip_address'])
            : null;

        $status = isset($sessionData['status'])
            ? SessionStatus::from($sessionData['status'])
            : SessionStatus::ACTIVE;

        $rawSessionData = json_encode($sessionData);

        return new SessionCreationData(
            sessionId: $sessionId,
            userId: $userId,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: $accessTokenExpiry,
            createdIp: $createdIp,
            lastIpAddress: $lastIpAddress,
            status: $status,
            csrfToken: $csrfToken,
            rawSessionData: $rawSessionData
        );
    }

}
