<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Mappers;

use App\Domain\User\Entities\User;
use App\Domain\Shared\Entities\EntityInterface;
use App\Infrastructure\Persistence\Shared\Mappers\DbMapperInterface;

/**
 * Interface UserDbMapperInterface
 * 
 * Defines contract for database mapper converting DB rows to User entity and back.
 */
interface UserDbMapperInterface extends DbMapperInterface
{
    /**
     * Converts database row array (including joined user, profile, roles data)
     * into a fully hydrated User entity.
     * 
     * @param array<string,mixed> $data Raw DB row data
     * @return User Fully constructed User entity
     */
    public function toEntity(array $data): User;

    /**
     * Converts a User entity into an associative array suitable for DB insert/update.
     * 
     * @param EntityInterface&User $entity User entity instance
     * @return array<string,mixed> Associative array of DB column => value
     */
    public function toDbArray(EntityInterface $entity): array;
}
