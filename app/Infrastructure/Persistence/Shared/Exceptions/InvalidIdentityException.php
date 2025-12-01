<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Shared\Exceptions;

class InvalidIdentityException extends MappingException
{
    /**
     * Named constructor for invalid or missing identity.
     */
    public static function missingIdentity(?string $identityData = null, ?\Throwable $previous = null): self
    {
        $message = $identityData === null
            ? 'Identity data is missing.'
            : sprintf('Invalid identity data provided: %s', $identityData);
        return new self($message, 0, $previous);
    }
}
