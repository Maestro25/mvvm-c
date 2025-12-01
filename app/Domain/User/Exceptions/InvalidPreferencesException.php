<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when invalid preferences data is used in domain.
 */
final class InvalidPreferencesException extends DomainException
{
    public static function invalidFormat(): self
    {
        return new self('Preferences data format is invalid.');
    }

    // Extend with more specific exceptions as needed
}
