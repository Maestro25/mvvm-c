<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Shared\Mappers;

use App\Domain\Shared\Entities\EntityInterface;

/**
 * Base DB Mapper for converting DB rows to Entities and vice versa.
 */
abstract class DbMapper implements DbMapperInterface
{
    /**
     * Convert a database row (array) to a domain entity.
     */
    abstract public function toEntity(array $data): EntityInterface;

    /**
     * Convert domain entity to database row (array) for persistence.
     */
    abstract public function toDbArray(EntityInterface $entity): array;
}
