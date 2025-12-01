<?php
declare(strict_types=1);

namespace App\Domain\Auth\Exceptions;

use App\Domain\Shared\Exceptions\ValueObjectException;

final class InvalidRememberMeTokenException extends ValueObjectException
{
    public static function fromInvalidToken(string $token): self
    {
        return new self("Invalid remember me token format: $token");
    }
}
