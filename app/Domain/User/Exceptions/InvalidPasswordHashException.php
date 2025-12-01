<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when a password hash is invalid according to domain rules.
 */
final class InvalidPasswordHashException extends DomainException
{
    /**
     * Factory method to create a new exception with contextual message.
     *
     * @param string $hash The invalid hash value.
     * @return static
     */
    public static function fromInvalidHash(string $hash): self
    {
        return new self(sprintf('Invalid password hash format: "%s".', $hash));
    }
}
