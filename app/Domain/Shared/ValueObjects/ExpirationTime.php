<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\TokenExpiredException;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

/**
 * Value Object representing the expiration time of a token.
 * Encapsulates validation and expiration checks.
 */
final class ExpirationTime extends ValueObject
{
    private DateTimeImmutable $expiresAt;

    public function __construct(DateTimeImmutable $expiresAt)
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expiresAtUtc = $expiresAt->setTimezone(new DateTimeZone('UTC'));

        if ($expiresAtUtc <= $now) {
            throw TokenExpiredException::create();
        }

        $this->expiresAt = $expiresAtUtc;
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        $now = $now ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
        return $now > $this->expiresAt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    protected function getAtomicValues(): array
    {
        return [$this->expiresAt->format(DATE_ATOM)];
    }

    public function __toString(): string
    {
        return $this->expiresAt->format(DATE_ATOM);
    }
}
