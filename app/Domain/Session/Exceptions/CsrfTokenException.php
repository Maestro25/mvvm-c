<?php
declare(strict_types=1);

namespace App\Domain\Session\Exceptions;

use App\Domain\Shared\Exceptions\ValueObjectException;

final class CsrfTokenException extends ValueObjectException
{
    public static function empty(): self
    {
        return new self('CSRF token cannot be empty.');
    }
}
