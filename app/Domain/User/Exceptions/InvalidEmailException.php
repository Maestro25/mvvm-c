<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when an invalid email value is used in domain.
 */
final class InvalidEmailException extends DomainException
{
    public static function fromInvalidEmail(string $email): self
    {
        return new self(sprintf('Invalid email address: "%s".', $email));
    }
}
