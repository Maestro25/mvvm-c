<?php
declare(strict_types=1);

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Entities\Permission;
use App\Domain\ValueObjects\PermissionId;

/**
 * Interface for Permission repository to abstract data access and persistence.
 */
interface PermissionRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a Permission by its unique identifier.
     *
     * @param PermissionId $id
     * @return Permission|null
     */
    public function getById(object $id): ?Permission;

    /**
     * Find a Permission by its name.
     *
     * @param string $name
     * @return Permission|null
     */
    public function findByName(string $name): ?Permission;

    /**
     * Get all permissions.
     *
     * @return Permission[]
     */
    public function getAll(): array;

    /**
     * Save (insert or update) a Permission entity.
     *
     * @param Permission $permission
     * @return bool
     */
    public function save(object $permission): bool;

    /**
     * Delete a Permission by id.
     *
     * @param PermissionId $id
     * @return bool
     */
    public function delete(object $id): bool;
}
