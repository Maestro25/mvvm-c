<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

final class UserAgentException extends ValueObjectException
{
    public static function empty(): self
    {
        return new self('User agent cannot be empty.');
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("User agent exceeds maximum length of $maxLength.");
    }
}
