<?php
declare(strict_types=1);

namespace App\Application\Session\Mappers;

use App\Application\Session\DTOs\SessionCreationData;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\Mappers\DtoMapperInterface;
use App\Domain\Session\Entities\Session;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;

interface SessionCreationDataMapperInterface extends DtoMapperInterface
{
    /**
     * Map Domain Entity to Creation DTO
     */
    public function toDTO(EntityInterface $entity): SessionCreationData;

    /**
     * Map Creation DTO to Domain Entity with provided identity
     */
    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): Session;
}
