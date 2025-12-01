<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidUuidException;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Immutable UUID Value Object wrapping ramsey/uuid.
 */
final class Uuid extends ValueObject implements IdentityInterface
{
    public readonly UuidInterface $uuid;

    public function __construct(string|UuidInterface $uuid) {
        if (is_string($uuid)) {
            if (!RamseyUuid::isValid($uuid)) {
                throw InvalidUuidException::fromInvalidUuid($uuid);
            }
            $this->uuid = RamseyUuid::fromString($uuid);
        } elseif ($uuid instanceof UuidInterface) {
            $this->uuid = $uuid;
        } else {
            throw InvalidUuidException::fromInvalidUuid((string)$uuid);
        }
    }

    /**
     * Static factory method to create Uuid VO from a UUID string.
     *
     * @param string $uuid
     * @return self
     * @throws InvalidUuidException if invalid UUID string
     */
    public static function fromString(string $uuid): self
    {
        // Validation is performed in constructor
        return new self($uuid);
    }

    /**
     * Generate a new random UUID v4 wrapped in Uuid VO.
     *
     * @return self
     */
    public static function generate(): self {
        return new self(RamseyUuid::uuid4());
    }

    protected function getAtomicValues(): array {
        return [$this->uuid->toString()];
    }

    public function __toString(): string {
        return $this->uuid->toString();
    }
}
