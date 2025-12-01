<?php
declare(strict_types=1);

namespace App\Domain\Shared\Entities;

use App\Domain\Shared\ValueObjects\IdentityInterface;

abstract class Entity implements EntityInterface
{
    public function __construct(
        protected readonly IdentityInterface $id
    ) {}

    public function equals(object $other): bool
    {
        if (!$other instanceof static) {
            return false;
        }
        return $this->id->equals($other->id);
    }

    // Optional: explicit toString() to represent the entity
    public function toString(): string
    {
        return (string)$this->id;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
