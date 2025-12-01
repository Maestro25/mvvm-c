<?php
declare(strict_types=1);

namespace App\Domain\Shared\Traits;

use InvalidArgumentException;

/**
 * Enum utility trait for enum helper methods.
 * Because abstract enums are not supported.
 */
trait EnumHelpers
{
    public static function isValid(string $value): bool
    {
        foreach (static::cases() as $case) {
            if ($case->value === $value) {
                return true;
            }
        }
        return false;
    }

    public static function fromValue(string $value): static
    {
        try {
            return static::from($value);
        } catch (\ValueError $e) {
            throw new InvalidArgumentException("Invalid value '$value' for enum " . static::class);
        }
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, static::cases());
    }

}
