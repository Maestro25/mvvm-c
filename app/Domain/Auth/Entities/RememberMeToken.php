<?php
declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use App\Domain\Shared\Entities\Entity;
use App\Domain\Shared\ValueObjects\RememeberMeTokenId;
use App\Domain\Shared\ValueObjects\UserId;

use App\Domain\Auth\ValueObjects\RememberToken;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\RememberMeTokenId;
use App\Domain\Shared\ValueObjects\TokenExpiry;
use DateTimeImmutable;
use InvalidArgumentException;
use LogicException;

/**
 * RememberMeToken entity
 * Represents a "remember me" persistent login token linked to a user,
 * with expiration and audit metadata.
 * Fully immutable; returns new instances on changes.
 */
final class RememberMeToken extends Entity
{
    public function __construct(
        RememberMeTokenId $id,
        private readonly UserId $userId,
        private readonly RememberToken $token,
        private readonly TokenExpiry $expiresAt,
        private readonly AuditInfo $createdInfo,
        private readonly ?AuditInfo $revokedInfo = null
    ) {
        if (empty((string) $id)) {
            throw new InvalidArgumentException('ID cannot be empty.');
        }
        parent::__construct($id);
    }

    public function getId(): RememberMeTokenId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getToken(): RememberToken
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

    public function getRevokedInfo(): ?AuditInfo
    {
        return $this->revokedInfo;
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        return $this->expiresAt->isExpired($now);
    }

    public function isRevoked(): bool
    {
        return $this->revokedInfo !== null;
    }

    /**
     * Revoke (delete) this token with a given audit info.
     * Returns new instance (immutable).
     *
     * @throws LogicException if already revoked
     */
    public function revoke(AuditInfo $revokedInfo): self
    {
        if ($this->isRevoked()) {
            throw new LogicException('Token is already revoked.');
        }

        return new self(
            $this->id,
            $this->userId,
            $this->token,
            $this->expiresAt,
            $this->createdInfo,
            $revokedInfo
        );
    }

    /**
     * Returns a new instance with updated expiration time.
     */
    public function withExpiresAt(DateTimeImmutable $expiresAt): self
    {
        return new self(
            $this->id,
            $this->userId,
            $this->token,
            new TokenExpiry($expiresAt),
            $this->createdInfo,
            $this->revokedInfo
        );
    }
}
