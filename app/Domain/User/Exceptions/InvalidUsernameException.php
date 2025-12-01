<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when an invalid email value is used in domain.
 */
final class InvalidUsernameException extends DomainException
{
    public static function lengthViolation(int $length): self
    {
        return new self("Username length invalid: {$length}, must be between 3 and 50 characters.");
    }

    public static function invalidFormat(): self
    {
        return new self("Username must start with a letter, can contain letters, digits, underscores, or hyphens, and must end with a letter or digit.");
    }
}

