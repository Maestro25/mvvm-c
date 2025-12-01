<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use Stringable;
use JsonSerializable;
use LogicException;

/**
 * Abstract base ValueObject class implementing immutability,
 * value-based equality, string casting, debugging info, and JSON serialization.
 */
abstract class ValueObject implements Stringable, JsonSerializable
{
    final public function __set(string $name, mixed $value): void {
        throw new LogicException('ValueObjects are immutable.');
    }

    abstract protected function getAtomicValues(): array;

    public function equals(object $other): bool {
        if (get_class($this) !== get_class($other)) {
            return false;
        }
        $values = $this->getAtomicValues();
        $otherValues = $other->getAtomicValues();
        if (count($values) !== count($otherValues)) {
            return false;
        }
        foreach ($values as $index => $value) {
            $otherValue = $otherValues[$index];
            if ($value instanceof static) {
                if (!$value->equals($otherValue)) return false;
            } elseif ($value !== $otherValue) {
                return false;
            }
        }
        return true;
    }

    abstract public function __toString(): string;

    public function jsonSerialize(): array {
        return $this->getAtomicValues();
    }

    public function __debugInfo(): array {
        return [
            'class' => static::class,
            'values' => $this->getAtomicValues(),
        ];
    }
}

