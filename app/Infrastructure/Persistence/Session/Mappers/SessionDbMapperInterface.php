<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Mappers;

use App\Domain\Session\Entities\Session;
use App\Domain\Shared\Entities\EntityInterface;
use App\Infrastructure\Persistence\Shared\Mappers\DbMapperInterface;

interface SessionDbMapperInterface extends DbMapperInterface
{
    /**
     * Map DB row (array) to Domain Entity
     *
     * @param array<string, mixed> $data
     * @return Session
     */
    public function toEntity(array $data): Session;

    /**
     * Map Domain Entity to DB row array
     *
     * @param EntityInterface $entity
     * @return array<string, mixed>
     */
    public function toDbArray(EntityInterface $entity): array;
}
