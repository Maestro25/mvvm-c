<?php
declare(strict_types=1);
namespace App\Domain\Repositories\Interfaces;

use App\Domain\ValueObjects\PermissionId;
use App\Domain\ValueObjects\RoleId;


interface RolePermissionRepositoryInterface extends RepositoryInterface
{
    public function assignPermissionToRole(RoleId $roleId, PermissionId $permissionId): bool;
    public function revokePermissionFromRole(RoleId $roleId, PermissionId $permissionId): bool;
    /**
     * @return PermissionId[] Permissions assigned to role
     */
    public function getPermissionsByRole(RoleId $roleId): array;
    public function getRolesByPermission(PermissionId $permissionId): array;
}


