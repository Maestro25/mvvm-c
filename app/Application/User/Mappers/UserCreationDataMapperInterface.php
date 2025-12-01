<?php
declare(strict_types=1);

namespace App\Application\User\Mappers;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\Mappers\DtoMapperInterface;
use App\Application\User\DTOs\UserCreationData;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\User\Entities\User;

interface UserCreationDataMapperInterface extends DtoMapperInterface
{
    /**
     * Map Domain Entity to Creation DTO
     */
    public function toDTO(EntityInterface $entity): UserCreationData;

    /**
     * Map Creation DTO to Domain Entity with provided identity
     */
    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): User;
}
