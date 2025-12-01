<?php
declare(strict_types=1);

namespace App\Domain\Session\Entities;

use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Session\Validation\Traits\SessionValidationGuard;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\Entities\Entity;

final class Session extends Entity implements SessionInterface
{

    private SessionStatus $status;

    public function __construct(
        private SessionId $sessionId,
        private readonly UserId $userId,
        private SessionToken $accessToken,
        private ?RefreshToken $refreshToken,
        private ?AuditInfo $createdInfo,
        private ExpirationTime $expiresAt,
        private IpAddress $createdIp,
        private ?IpAddress $lastIpAddress = null,
        private ?AuditInfo $updatedInfo = null,
        private ?CsrfToken $csrfToken = null,
        private ?string $rawSessionData = null,
        ?SessionStatus $status = null
    ) {
        parent::__construct($sessionId);
        $this->status = $status ?? SessionStatus::ACTIVE;
    }

    public function getId(): SessionId
    {
        return $this->sessionId;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getAccessToken(): SessionToken
    {
        return $this->accessToken;
    }

    public function setAccessToken(SessionToken $token): void
    {
        $this->accessToken = $token;
    }

    public function getRefreshToken(): ?RefreshToken
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?RefreshToken $token): void
    {
        $this->refreshToken = $token;
    }

    public function getCsrfToken(): ?CsrfToken
    {
        return $this->csrfToken;
    }

    public function setCsrfToken(?CsrfToken $token): void
    {
        $this->csrfToken = $token;
    }

    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    public function getExpiresAt(): ExpirationTime
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(ExpirationTime $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function getCreatedIp(): IpAddress
    {
        return $this->createdIp;
    }

    public function getLastIpAddress(): ?IpAddress
    {
        return $this->lastIpAddress;
    }

    public function setLastIpAddress(IpAddress $ipAddress): void
    {
        $this->lastIpAddress = $ipAddress;
        $this->setUpdatedInfo(new AuditInfo(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $this->userId,
            $this->lastIpAddress
        ));
    }

    public function getUpdatedInfo(): ?AuditInfo
    {
        return $this->updatedInfo;
    }

    public function setUpdatedInfo(AuditInfo $updatedInfo): void
    {
        $this->updatedInfo = $updatedInfo;
    }

    public function getStatus(): SessionStatus
    {
        // Dynamically update expired status
        if ($this->status === SessionStatus::ACTIVE && $this->isExpired()) {
            $this->status = SessionStatus::EXPIRED;
        }
        return $this->status;
    }

    public function revoke(): void
    {
        $this->status = SessionStatus::REVOKED;
        $this->setUpdatedInfo(new AuditInfo(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $this->userId,
            $this->lastIpAddress
        ));
    }

    public function isExpired(?\DateTimeImmutable $now = null): bool
    {
        return $this->expiresAt->isExpired($now);
    }

    public function renew(ExpirationTime $newExpiry): void
    {
        $this->expiresAt = $newExpiry;
        $this->status = SessionStatus::ACTIVE; // Reactivate upon renewal
        $this->setUpdatedInfo(new AuditInfo(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $this->userId,
            $this->lastIpAddress
        ));
    }

    public function getRawSessionData(): ?string
    {
        return $this->rawSessionData;
    }

    public function setRawSessionData(?string $data): void
    {
        $this->rawSessionData = $data;
    }
    public function isActive(): bool
    {
        return $this->getStatus() === SessionStatus::ACTIVE;
    }
    public function updateTokens(
        SessionToken $accessToken,
        ?RefreshToken $refreshToken,
        ?CsrfToken $csrfToken,
        ExpirationTime $expiresAt
    ): void {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->csrfToken = $csrfToken;
        $this->expiresAt = $expiresAt;

        // Optionally reactivate session on token update
        $this->status = SessionStatus::ACTIVE;

        $this->setUpdatedInfo(new AuditInfo(
            new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
            $this->userId,
            $this->lastIpAddress
        ));

    }


}
