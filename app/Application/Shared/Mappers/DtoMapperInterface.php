<?php
declare(strict_types=1);

namespace App\Application\Shared\Mappers;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;

interface DtoMapperInterface
{
    /**
     * Map entity to DTO.
     */
    public function toDTO(EntityInterface $entity): EntityCreationDataInterface;

    /**
     * Map DTO and ID to entity.
     */
    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): EntityInterface;
}
