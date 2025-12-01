<?php
declare(strict_types=1);

namespace App\Domain\Shared\Entities;

use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\ValueObjects\TokenExpiry;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\ValueObject;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * BaseTokenEntity is an abstract entity representing common token fields and behavior.
 * It enforces immutability of its core attributes and provides shared token lifecycle methods.
 */
final class Token extends ValueObject
{
    public function __construct(
        private readonly UserId $userId,
        private readonly string $tokenValue,
        private readonly TokenExpiry $expiresAt,
        private readonly AuditInfo $createdInfo,
        private readonly ?AuditInfo $usedInfo = null
    ) {
        if (empty($tokenValue)) {
            throw new InvalidArgumentException('Token value cannot be empty.');
        }
    }

    protected function getAtomicValues(): array
    {
        return [
            $this->userId,
            $this->tokenValue,
            $this->expiresAt,
            $this->createdInfo,
            $this->usedInfo,
        ];
    }

    public function markUsed(AuditInfo $usedInfo): self
    {
        if ($this->isUsed()) {
            throw new \LogicException('Token is already used.');
        }
        return new self(
            $this->userId,
            $this->tokenValue,
            $this->expiresAt,
            $this->createdInfo,
            $usedInfo
        );
    }

    public function isUsed(): bool
    {
        return $this->usedInfo !== null;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt->isExpired();
    }

    public function __toString(): string
    {
        return $this->tokenValue;
    }
}

