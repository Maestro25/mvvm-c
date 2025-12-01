<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

final class JwtTokenException extends ValueObjectException
{
    public static function emptyToken(): self
    {
        return new self('JWT token cannot be empty.');
    }

    public static function expiredToken(): self
    {
        return new self('Token expiration must be a future time.');
    }
}
