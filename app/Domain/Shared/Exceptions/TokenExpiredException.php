<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a token is expired.
 */
final class TokenExpiredException extends RuntimeException
{
    public static function create(string $message = 'Token has expired.'): self
    {
        return new self($message);
    }
}
