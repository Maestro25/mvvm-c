<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when an invalid address value is used in domain.
 */
final class InvalidAddressException extends DomainException
{
    public static function fieldLengthViolation(string $fieldName, int $givenLength, int $maxLength): self
    {
        return new self(sprintf(
            '%s length invalid: %d, must be at most %d characters.',
            $fieldName,
            $givenLength,
            $maxLength
        ));
    }

    // Add other specific validation exceptions as needed
}
