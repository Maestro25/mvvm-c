<?php
declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\RoleId;
use App\Domain\Enums\UserRole;
use App\Domain\Enums\PermissionLevel;
use App\Domain\Enums\PermissionName;
use App\Domain\ValueObjects\AuditInfo;
use App\Domain\ValueObjects\IpAddress;
use App\Domain\ValueObjects\UserId;
use DateTimeImmutable;

/**
 * Role entity encapsulating role identity, name, description,
 * permissions as entities, and audit metadata.
 */
final class Role extends Entity
{
    private RoleId $roleId;

    private UserRole $name;

    private ?string $description;

    /** @var Permission[] */
    private array $permissions;

    private AuditInfo $createdInfo;
    private AuditInfo $updatedInfo;
    private ?AuditInfo $deletedInfo;

    /**
     * Role constructor.
     *
     * @param RoleId $roleId
     * @param UserRole $name
     * @param string|null $description
     * @param AuditInfo $createdInfo
     * @param AuditInfo $updatedInfo
     * @param Permission[] $permissions
     * @param AuditInfo|null $deletedInfo
     */
    public function __construct(
        RoleId $roleId,
        UserRole $name,
        ?string $description,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo,
        array $permissions = [],
        ?AuditInfo $deletedInfo = null,
    ) {
        parent::__construct($roleId);

        $this->roleId = $roleId;
        $this->name = $name;
        $this->description = $description;
        $this->permissions = $permissions;
        $this->createdInfo = $createdInfo;
        $this->updatedInfo = $updatedInfo;
        $this->deletedInfo = $deletedInfo;
    }

    // Identity getters
    public function getId(): RoleId
    {
        return $this->roleId;
    }

    public function __toString(): string
    {
        return (string) $this->roleId;
    }

    // Basic properties getters/setters

    public function getName(): UserRole
    {
        return $this->name;
    }

    public function setName(UserRole $name, AuditInfo $updatedInfo): void
    {
        if ($this->name !== $name) {
            $this->name = $name;
            $this->touchUpdatedInfo($updatedInfo);
        }
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description, AuditInfo $updatedInfo): void
    {
        if ($this->description !== $description) {
            $this->description = $description;
            $this->touchUpdatedInfo($updatedInfo);
        }
    }

    /**
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        // Return a copy for encapsulation
        return $this->permissions;
    }

    public function addPermission(Permission $permission, AuditInfo $updatedInfo): void
    {
        foreach ($this->permissions as $perm) {
            if ($perm->getId()->equals($permission->getId())) {
                return; // Already assigned
            }
        }

        $this->permissions[] = $permission;
        $this->touchUpdatedInfo($updatedInfo);
    }

    public function removePermission(Permission $permission, AuditInfo $updatedInfo): void
    {
        foreach ($this->permissions as $key => $perm) {
            if ($perm->getId()->equals($permission->getId())) {
                unset($this->permissions[$key]);
                $this->permissions = array_values($this->permissions); // Reindex
                $this->touchUpdatedInfo($updatedInfo);
                return;
            }
        }
    }

    public function hasPermission(PermissionName $permissionName, PermissionLevel $level): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getName()->value === $permissionName &&
                $permission->getLevel() === $level) {
                return true;
            }
        }
        return false;
    }

    // AuditInfo getters
    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    public function getUpdatedInfo(): AuditInfo
    {
        return $this->updatedInfo;
    }

    public function getDeletedInfo(): ?AuditInfo
    {
        return $this->deletedInfo;
    }

    private function touchUpdatedInfo(AuditInfo $updatedInfo): void
    {
        $this->updatedInfo = $updatedInfo;
    }

    // Convenience audit field getters

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdInfo->getTimestamp();
    }

    public function getCreatedBy(): ?UserId
    {
        return $this->createdInfo->getUserId();
    }

    public function getCreatedIp(): ?IpAddress
    {
        return $this->createdInfo->getIpAddress();
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedInfo->getTimestamp();
    }

    public function getUpdatedBy(): ?UserId
    {
        return $this->updatedInfo->getUserId();
    }

    public function getUpdatedIp(): ?IpAddress
    {
        return $this->updatedInfo->getIpAddress();
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedInfo?->getTimestamp();
    }

    public function getDeletedBy(): ?UserId
    {
        return $this->deletedInfo?->getUserId();
    }

    public function getDeletedIp(): ?IpAddress
    {
        return $this->deletedInfo?->getIpAddress();
    }
}
