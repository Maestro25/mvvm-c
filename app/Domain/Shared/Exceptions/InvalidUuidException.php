<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use DomainException;

class InvalidUuidException extends DomainException
{
    public static function fromInvalidUuid(string $uuid): self
    {
        return new self(sprintf('Invalid UUID provided: %s', $uuid));
    }
}
