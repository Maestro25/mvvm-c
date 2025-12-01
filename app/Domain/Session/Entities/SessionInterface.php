<?php
declare(strict_types=1);

namespace App\Domain\Session\Entities;

use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\UserId;

interface SessionInterface extends EntityInterface
{
    public function getId(): SessionId;
    public function getUserId(): UserId;

    public function getAccessToken(): SessionToken;
    public function setAccessToken(SessionToken $token): void;

    public function getRefreshToken(): ?RefreshToken;
    public function setRefreshToken(?RefreshToken $token): void;

    public function getCsrfToken(): ?CsrfToken;
    public function setCsrfToken(?CsrfToken $token): void;

    public function getCreatedInfo(): ?AuditInfo;

    public function getExpiresAt(): ExpirationTime;
    public function setExpiresAt(ExpirationTime $expiresAt): void;

    public function getCreatedIp(): IpAddress;

    public function getLastIpAddress(): ?IpAddress;
    public function setLastIpAddress(IpAddress $ipAddress): void;

    public function getUpdatedInfo(): ?AuditInfo;
    public function setUpdatedInfo(AuditInfo $updatedInfo): void;

    public function getStatus(): SessionStatus;
    public function isActive(): bool;
    public function isExpired(?\DateTimeImmutable $now = null): bool;

    public function revoke(): void;
    public function renew(ExpirationTime $newExpiry): void;

    public function getRawSessionData(): ?string;
    public function setRawSessionData(?string $data): void;

    /**
     * Atomically update all tokens and expiration, reacting state accordingly.
     */
    public function updateTokens(
        SessionToken $accessToken,
        ?RefreshToken $refreshToken,
        ?CsrfToken $csrfToken,
        ExpirationTime $expiresAt
    ): void;
}
