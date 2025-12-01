<?php
declare(strict_types=1);

namespace App\Domain\Factories\Interfaces;

use App\Domain\Entities\Role;
use App\Domain\Enums\UserRole;

interface RoleFactoryInterface extends FactoryInterface
{
    /**
     * Create a Role entity.
     *
     * @param mixed ...$params Expecting:
     *  - RoleId $roleId
     *  - UserRole $name
     *  - ?string $description (optional)
     * @return Role
     */
    public function create(...$params): Role;
}
