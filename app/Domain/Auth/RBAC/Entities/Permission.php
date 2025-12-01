<?php
declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Enums\PermissionLevel;
use App\Domain\Enums\PermissionName;
use App\Domain\ValueObjects\PermissionId;
use App\Domain\ValueObjects\AuditInfo;
use DateTimeImmutable;

/**
 * Permission entity representing a specific permission with audit metadata.
 */
final class Permission
{
    private PermissionId $id;
    private PermissionName $name;
    private PermissionLevel $level;
    private ?string $description;
    private AuditInfo $createdInfo;
    private AuditInfo $updatedInfo;

    /**
     * Permission constructor.
     *
     * @param PermissionId $id
     * @param PermissionName $name
     * @param PermissionLevel $level
     * @param string|null $description
     * @param AuditInfo $createdInfo
     * @param AuditInfo $updatedInfo
     */
    public function __construct(
        PermissionId $id,
        PermissionName $name,
        PermissionLevel $level,
        ?string $description,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->description = $description;
        $this->createdInfo = $createdInfo;
        $this->updatedInfo = $updatedInfo;
    }

    /**
     * Get the permission ID.
     */
    public function getId(): PermissionId
    {
        return $this->id;
    }

    /**
     * Get the permission name enum.
     */
    public function getName(): PermissionName
    {
        return $this->name;
    }

    /**
     * Get the permission level enum.
     */
    public function getLevel(): PermissionLevel
    {
        return $this->level;
    }

    /**
     * Set permission level and update audit info.
     */
    public function setLevel(PermissionLevel $level, AuditInfo $updatedInfo): void
    {
        if ($this->level !== $level) {
            $this->level = $level;
            $this->touchUpdatedInfo($updatedInfo);
        }
    }

    /**
     * Get permission description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set permission description and update audit info.
     */
    public function setDescription(?string $description, AuditInfo $updatedInfo): void
    {
        if ($this->description !== $description) {
            $this->description = $description;
            $this->touchUpdatedInfo($updatedInfo);
        }
    }

    /**
     * Get created audit info.
     */
    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    /**
     * Get updated audit info.
     */
    public function getUpdatedInfo(): AuditInfo
    {
        return $this->updatedInfo;
    }

    /**
     * Update the updatedInfo field.
     */
    private function touchUpdatedInfo(AuditInfo $updatedInfo): void
    {
        $this->updatedInfo = $updatedInfo;
    }
}
