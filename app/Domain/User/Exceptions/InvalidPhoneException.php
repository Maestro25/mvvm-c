<?php
declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use DomainException;

/**
 * Exception thrown when an invalid phone number is used in domain.
 */
final class InvalidPhoneException extends DomainException
{
    public static function invalidFormat(string $phone): self
    {
        return new self("Phone number format is invalid: {$phone}");
    }
}
