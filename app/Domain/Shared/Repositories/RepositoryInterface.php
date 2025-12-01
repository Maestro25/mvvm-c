<?php
declare(strict_types=1);

namespace App\Domain\Shared\Repositories;

use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Entities\EntityInterface;

interface RepositoryInterface
{
    /**
     * Find an entity by its identity.
     *
     * @param IdentityInterface $id
     * @return EntityInterface|null
     */
    public function getById(IdentityInterface $id): ?EntityInterface;

    /**
     * Retrieve all entities.
     *
     * @return EntityInterface[]
     */
    public function getAll(): array;

    /**
     * Save (insert or update) an entity.
     *
     * @param EntityInterface $entity
     * @return bool Success flag
     */
    public function save(EntityInterface $entity): bool;

    /**
     * Soft delete an entity by ID.
     *
     * @param IdentityInterface $id
     * @param IdentityInterface|null $actorId Optional actor performing deletion
     * @param string|null $ipBinary Optional IP address in binary format
     * @return bool Success flag
     */
    public function delete(
        IdentityInterface $id,
        ?IdentityInterface $actorId = null,
        ?string $ipBinary = null
    ): bool;

    /**
     * Begin database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commit database transaction.
     */
    public function commit(): void;

    /**
     * Roll back database transaction.
     */
    public function rollBack(): void;
}
