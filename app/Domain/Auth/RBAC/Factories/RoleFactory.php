<?php
declare(strict_types=1);

namespace App\Domain\Factories;

use App\Domain\{
    Entities\Permission,
    Factories\Interfaces\RoleFactoryInterface,
    Entities\Role,
    Enums\UserRole,
    ValueObjects\RoleId,
    ValueObjects\AuditInfo,
    ValueObjects\UserId,
    ValueObjects\IpAddress,
};

use InvalidArgumentException;

final class RoleFactory extends Factory implements RoleFactoryInterface
{
    /**
     * Create Role entity with strict typing, audit info consistency.
     * 
     * @param mixed ...$params
     * @return Role
     * 
     * @throws InvalidArgumentException
     */
    public function create(...$params): Role
    {
        [
            $roleId,
            $name,
            $description,
            $createdAt,
            $createdBy,
            $createdIp,
            $updatedAt,
            $updatedBy,
            $updatedIp,
            $permissions,
            $deletedInfo
        ] = array_pad($params, 11, null);

        $this->assertInstance($roleId, RoleId::class, 'roleId');
        $this->assertInstance($name, UserRole::class, 'name');

        if ($description !== null && !is_string($description)) {
            throw new InvalidArgumentException('Description must be a string or null.');
        }

        if ($createdBy !== null) {
            $this->assertInstance($createdBy, UserId::class, 'createdBy');
        }
        if ($updatedBy !== null) {
            $this->assertInstance($updatedBy, UserId::class, 'updatedBy');
        }
        if ($createdIp !== null) {
            $this->assertInstance($createdIp, IpAddress::class, 'createdIp');
        }
        if ($updatedIp !== null) {
            $this->assertInstance($updatedIp, IpAddress::class, 'updatedIp');
        }
        if ($deletedInfo !== null) {
            $this->assertInstance($deletedInfo, AuditInfo::class, 'deletedInfo');
        }

        // Normalize dates and build audit info
        $createdAtUtc = $createdAt !== null ? $createdAt->setTimezone(new \DateTimeZone('UTC')) : null;
        $updatedAtUtc = $updatedAt !== null ? $updatedAt->setTimezone(new \DateTimeZone('UTC')) : null;

        $auditInfos = $this->getCommonAuditInfo([
            'createdAt' => $createdAtUtc,
            'createdBy' => $createdBy,
            'createdIp' => $createdIp,
            'updatedAt' => $updatedAtUtc,
            'updatedBy' => $updatedBy,
            'updatedIp' => $updatedIp,
        ]);

        $createdInfo = $auditInfos['createdInfo'];
        $updatedInfo = $auditInfos['updatedInfo'];

        // Validate permissions or default to empty array
        if ($permissions === null) {
            $permissions = [];
        } elseif (!is_array($permissions)) {
            throw new InvalidArgumentException('Permissions must be an array of Permission objects.');
        } else {
            foreach ($permissions as $permission) {
                $this->assertInstance($permission, Permission::class, 'permissions item');
            }
        }

        return new Role(
            $roleId,
            $name,
            $description,
            $createdInfo,
            $updatedInfo,
            $permissions,
            $deletedInfo
        );
    }
}
