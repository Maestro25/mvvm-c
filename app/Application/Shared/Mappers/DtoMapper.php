<?php
declare(strict_types=1);

namespace App\Application\Shared\Mappers;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;

/**
 * Base DTO Mapper interface defining conversion between DTO and Entity.
 */
abstract class DtoMapper implements DtoMapperInterface
{
    /**
     * Map domain Entity to Data Transfer Object.
     */
    abstract public function toDTO(EntityInterface $entity): EntityCreationDataInterface;

    /**
     * Map Data Transfer Object to domain Entity using Identity.
     */
    abstract public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): EntityInterface;
}
