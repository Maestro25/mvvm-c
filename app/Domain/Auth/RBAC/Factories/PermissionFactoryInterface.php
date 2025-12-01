<?php
declare(strict_types=1);

namespace App\Domain\Factories\Interfaces;

use App\Domain\Entities\Permission;
use App\Domain\Enums\PermissionName;
use App\Domain\Enums\PermissionLevel;
use DateTimeImmutable;

interface PermissionFactoryInterface extends FactoryInterface
{
    /**
     * Create a Permission entity.
     *
     * @param mixed ...$params Expecting:
     *  - PermissionId $permissionId
     *  - PermissionName $name
     *  - PermissionLevel $level
     *  - ?string $description (optional)
     *  - ?DateTimeImmutable $createdAt (optional)
     *  - ?DateTimeImmutable $updatedAt (optional)
     * @return Permission
     */
    public function create(...$params): Permission;
}
