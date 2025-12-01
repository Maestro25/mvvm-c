<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

final class FailedLoginAttempts
{
    private int $value;

    private function __construct(int $value) {
        if ($value < 0) {
            throw new \InvalidArgumentException('Failed login attempts cannot be negative');
        }
        $this->value = $value;
    }

    public static function fromInt(int $value): self {
        return new self($value);
    }

    public static function zero(): self {
        return new self(0);
    }

    public function getValue(): int {
        return $this->value;
    }

    public function increment(): self {
        return new self($this->value + 1);
    }

    public function __toString(): string {
        return (string)$this->value;
    }
}
