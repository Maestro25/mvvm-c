<?php
declare(strict_types=1);

namespace App\Domain\Auth\Factories;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;

interface AuthInfoFactoryInterface
{
    /**
     * Create an entity from a given Identity and DTO.
     *
     * @param IdentityInterface $id
     * @param EntityCreationDataInterface $dto
     * @return EntityInterface
     */
    public function createEntity(IdentityInterface $id, EntityCreationDataInterface $dto): EntityInterface;
}
