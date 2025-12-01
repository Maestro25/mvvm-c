<?php
// Domain exceptions - located in App\Domain\Exceptions

namespace App\Domain\Shared\Exceptions;

/**
 * Exception thrown when an entity is not found in the domain.
 */
class EntityNotFoundException extends RepositoryException
{
    public static function forId(string $id): self
    {
        return new self("Entity with ID '{$id}' was not found.");
    }
}

