<?php
declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use App\Domain\Auth\ValueObjects\VerificationToken;
use App\Domain\Shared\Entities\Entity;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\TokenExpiry;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\VerificationTokenId;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

/**
 * EmailVerificationToken entity representing a user's email verification token lifecycle,
 * including audit metadata via AuditInfo value objects.
 */
final class EmailVerificationToken extends Entity
{
    public function __construct(
        VerificationTokenId $id,
        private readonly UserId $userId,
        private readonly VerificationToken $token,
        private readonly TokenExpiry $expiresAt,
        private readonly AuditInfo $createdInfo,
        private readonly ?AuditInfo $usedInfo = null
    ) {
        if (empty((string)$id)) {
            throw new InvalidArgumentException('ID cannot be empty.');
        }
        parent::__construct($id);
    }

    public function getId(): VerificationTokenId
    {
        return $this->id; // inherited from Entity
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getToken(): VerificationToken
    {
        return $this->token;
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

    /**
     * Marks token as used with given usage AuditInfo.
     *
     * @throws LogicException if token is already used
     */
    public function markUsed(AuditInfo $usedInfo): self
    {
        if ($this->isUsed()) {
            throw new LogicException('Token is already used.');
        }
        
        return new self(
            $this->id,
            $this->userId,
            $this->token,
            $this->expiresAt,
            $this->createdInfo,
            $usedInfo
        );
    }
}
