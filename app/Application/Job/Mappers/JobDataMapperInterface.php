<?php
declare(strict_types=1);

namespace App\Application\Job\Mappers;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\Mappers\DtoMapperInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;

interface JobDataMapperInterface extends DtoMapperInterface
{
    /**
     * Maps a Job Entity to a JobData DTO
     *
     * @param EntityInterface $entity
     * @return EntityCreationDataInterface
     */
    public function toDTO(EntityInterface $entity): EntityCreationDataInterface;

    /**
     * Maps a JobData DTO to a Job Entity
     *
     * @param IdentityInterface $id
     * @param EntityCreationDataInterface $dto
     * @return EntityInterface
     */
    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): EntityInterface;
}
