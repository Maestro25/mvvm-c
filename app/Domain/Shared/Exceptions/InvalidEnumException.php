<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

final class InvalidEnumException extends ValueObjectException
{
    public static function invalid(string $timezoneName): self
    {
        return new self("Invalid timezone identifier '{$timezoneName}'.");
    }
}
