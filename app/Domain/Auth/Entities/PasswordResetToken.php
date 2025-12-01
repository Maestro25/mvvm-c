<?php
declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use App\Domain\Auth\ValueObjects\ResetToken;
use App\Domain\Shared\Entities\Entity;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\ResetTokenId;
use App\Domain\Shared\ValueObjects\TokenExpiry;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

/**
 * Password Reset Token Entity
 *
 * Represents a password reset token assigned to a user with expiration and usage tracking.
 * Implements full immutability. All date/time values are expected in UTC.
 */
final class PasswordResetToken extends Entity
{
    public function __construct(
        ResetTokenId $id,
        private readonly UserId $userId,
        private readonly ResetToken $resetToken,
        private readonly TokenExpiry $expiresAt,
        private readonly AuditInfo $createdInfo,
        private readonly ?AuditInfo $usedInfo = null
    ) {
        if (empty((string) $id)) {
            throw new InvalidArgumentException('ID cannot be empty.');
        }
        parent::__construct($id);
    }

    public function getId(): ResetTokenId
    {
        return $this->id; // inherited from Entity
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getResetToken(): ResetToken
    {
        return $this->resetToken;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt->getExpiresAt();
    }

    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    public function getUsedInfo(): ?AuditInfo
    {
        return $this->usedInfo;
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        return $this->expiresAt->isExpired($now);
    }

    public function isUsed(): bool
    {
        return $this->usedInfo !== null;
    }

    public function markUsed(AuditInfo $usedInfo): self
    {
        if ($this->isUsed()) {
            throw new LogicException('Token is already used.');
        }

        return new self(
            $this->id,
            $this->userId,
            $this->resetToken,
            $this->expiresAt,
            $this->createdInfo,
            $usedInfo
        );
    }

    public function withExpiresAt(DateTimeImmutable $expiresAt): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->resetToken,
            new TokenExpiry($expiresAt),
            $this->createdInfo,
            $this->usedInfo
        );
    }
}
