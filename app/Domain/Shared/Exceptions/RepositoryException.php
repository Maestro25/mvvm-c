<?php
// Domain exceptions - located in App\Domain\Exceptions

namespace App\Domain\Shared\Exceptions;

use RuntimeException;

/**
 * Base exception for repository-related domain errors.
 */
class RepositoryException extends RuntimeException
{
    /**
     * Named constructor for generic repository errors.
     */
    public static function generic(string $message = 'Repository error occurred.', int $code = 0, ?\Throwable $previous = null): self
    {
        return new self($message, $code, $previous);
    }
}

