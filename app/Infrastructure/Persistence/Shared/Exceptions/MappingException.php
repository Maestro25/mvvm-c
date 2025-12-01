<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Shared\Exceptions;

use RuntimeException;

class MappingException extends RuntimeException
{
    /**
     * Named constructor for general mapping failure.
     */
    public static function failed(string $operation = 'mapping', ?\Throwable $previous = null): self
    {
        $message = sprintf('Mapping failure occurred during %s.', $operation);
        return new self($message, 0, $previous);
    }
}

