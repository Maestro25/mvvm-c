<?php
// Persistence exceptions - App\Infrastructure\Persistence\Exceptions namespace

namespace App\Infrastructure\Persistence\Shared\Exceptions;

use RuntimeException;

class PersistenceException extends RuntimeException
{
    /**
     * Named constructor for persistence failure.
     */
    public static function failure(string $operation = 'operation', ?\Throwable $previous = null): self
    {
        $message = sprintf('Persistence failure during %s.', $operation);
        return new self($message, 0, $previous);
    }
}
