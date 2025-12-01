<?php
declare(strict_types=1);
namespace App\Domain\Repositories\Interfaces;

use App\Domain\ValueObjects\RoleId;
use App\Domain\ValueObjects\UserId;

interface UserRoleRepositoryInterface
{
    public function assignRoleToUser(UserId $userId, RoleId $roleId): bool;
    public function revokeRoleFromUser(UserId $userId, RoleId $roleId): bool;
    /**
     * @return RoleId[] Roles assigned to user
     */
    public function getRolesByUser(UserId $userId): array;
    public function userHasRole(UserId $userId, RoleId $roleId): bool;
}


