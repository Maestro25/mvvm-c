<?php
declare(strict_types=1);

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Entities\Role;
use App\Domain\ValueObjects\RoleId;

/**
 * Interface for Role repository to abstract data access and persistence.
 */
interface RoleRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a Role by its unique identifier.
     * 
     * @param RoleId $id
     * @return Role|null
     */
    public function getById(object $id): ?Role;

    /**
     * Find a Role by its name.
     * 
     * @param string $name
     * @return Role|null
     */
    public function findByName(string $name): ?Role;

    /**
     * Get all roles.
     * 
     * @return Role[]
     */
    public function getAll(): array;

    /**
     * Save (insert or update) a Role entity.
     * 
     * @param Role $role
     * @return bool
     */
    public function save(object $role): bool;
}
