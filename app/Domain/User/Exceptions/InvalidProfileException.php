<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown for errors related to Profile domain.
 */
final class InvalidProfileException extends DomainException
{
    public static function profileIncomplete(string $message = ''): self
    {
        $defaultMessage = 'Profile incomplete: required fields missing.';
        return new self($message ?: $defaultMessage);
    }

    public static function invalidState(string $message): self
    {
        return new self($message);
    }

    // Extend with other profile-specific exceptions as needed
}
