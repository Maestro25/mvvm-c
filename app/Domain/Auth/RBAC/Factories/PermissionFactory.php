<?php
declare(strict_types=1);

namespace App\Domain\Factories;

use App\Domain\{
    Factories\Interfaces\PermissionFactoryInterface,
    Entities\Permission,
    Enums\PermissionName,
    Enums\PermissionLevel,
    ValueObjects\PermissionId,
    ValueObjects\AuditInfo,
    ValueObjects\UserId,
    ValueObjects\IpAddress,
};


use DateTimeImmutable;
use InvalidArgumentException;

final class PermissionFactory extends Factory implements PermissionFactoryInterface
{
    /**
     * Create Permission entity with strong typing and audit info.
     * 
     * @param mixed ...$params
     * @return Permission
     * 
     * @throws InvalidArgumentException
     */
    public function create(...$params): Permission
    {
        [
            $permissionId,
            $name,
            $level,
            $description,
            $createdAt,
            $createdBy,
            $createdIp,
            $updatedAt,
            $updatedBy,
            $updatedIp
        ] = array_pad($params, 10, null);

        $this->assertInstance($permissionId, PermissionId::class, 'permissionId');
        $this->assertInstance($name, PermissionName::class, 'name');
        $this->assertInstance($level, PermissionLevel::class, 'level');

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

        return new Permission(
            $permissionId,
            $name,
            $level,
            $description,
            $auditInfos['createdInfo'],
            $auditInfos['updatedInfo']
        );
    }
}
