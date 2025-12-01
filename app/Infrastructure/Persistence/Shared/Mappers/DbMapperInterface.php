<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Shared\Mappers;

use App\Domain\Shared\Entities\EntityInterface;

interface DbMapperInterface
{
    /**
     * Convert database row (array) to entity.
     */
    public function toEntity(array $data): EntityInterface;

    /**
     * Convert entity to array suitable for DB persistence.
     */
    public function toDbArray(EntityInterface $entity): array;
}
