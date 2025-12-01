<?php
declare(strict_types=1);

namespace App\Domain\Repositories\Interfaces;

use App\Domain\Entities\Profile;
use App\Domain\ValueObjects\ProfileId;

interface ProfileRepositoryInterface extends RepositoryInterface
{
    public function getById(object $id): ?Profile;

    /**
     * @return Profile[]
     */
    public function getAll(): array;

    public function save(object $entity, ?ProfileId $actorId = null, ?string $ipBinary = null): bool;

    public function delete(object $id, ?object $actorId = null, ?string $ipBinary = null): bool;
}
