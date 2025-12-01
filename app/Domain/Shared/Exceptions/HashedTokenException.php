<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

final class HashedTokenException extends ValueObjectException
{
    public static function invalidFormat(string $token): self
    {
        return new self("Invalid token format: $token");
    }
}
