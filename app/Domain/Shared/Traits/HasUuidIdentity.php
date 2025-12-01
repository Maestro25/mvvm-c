<?php
declare(strict_types=1);

namespace App\Domain\Shared\Traits;

use App\Domain\Shared\Exceptions\InvalidUuidException;
use App\Domain\Shared\ValueObjects\Uuid;

trait HasUuidIdentity
{
    private readonly Uuid $uuid;

    /**
     * Initialize UUID from a string or Uuid VO.
     *
     * @param string|Uuid $uuid
     * @throws InvalidUuidException
     */
    public function initUuid(string|Uuid $uuid): void
    {
        if (is_string($uuid)) {
            $this->uuid = Uuid::fromString($uuid);
        } elseif ($uuid instanceof Uuid) {
            $this->uuid = $uuid;
        } else {
            throw InvalidUuidException::fromInvalidUuid((string)$uuid);
        }
    }

    /**
     * Static generator shortcut.
     *
     * @return static
     */
    public static function generate(): self
    {
        return new self(Uuid::generate());
    }

    /**
     * Static factory method for creating from UUID string.
     *
     * @param string $uuidString
     * @return static
     * @throws InvalidUuidException
     */
    public static function fromString(string $uuidString): self
    {
        return new self(Uuid::fromString($uuidString));
    }

    /**
     * String representation delegated to Uuid.
     */
    public function __toString(): string
    {
        return (string)$this->uuid;
    }

    /**
     * Equals check delegated to composed Uuid.
     */
    public function equals(object $other): bool
    {
        if (!$other instanceof static) {
            return false;
        }

        return $this->uuid->equals($other->uuid);
    }

    /**
     * Expose composed Uuid VO.
     */
    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
