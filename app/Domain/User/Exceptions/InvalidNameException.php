<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when an invalid name value is used in domain.
 */
final class InvalidNameException extends DomainException
{
    public static function firstNameLengthViolation(int $length): self
    {
        return new self("First name length invalid: {$length}, must be between 1 and 100 characters.");
    }

    public static function invalidFirstNameFormat(): self
    {
        return new self("First name contains invalid characters. Allowed: letters, spaces, hyphens, apostrophes.");
    }

    public static function lastNameLengthViolation(int $length): self
    {
        return new self("Last name length invalid: {$length}, must be between 1 and 100 characters.");
    }

    public static function invalidLastNameFormat(): self
    {
        return new self("Last name contains invalid characters. Allowed: letters, spaces, hyphens, apostrophes.");
    }
}
