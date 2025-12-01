<?php
declare(strict_types=1);

namespace App\Domain\Shared\Factories;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\DTOs\AuditDataInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;

/**
 * Interface for a factory that creates entities from entity DTO and audit DTO.
 */
interface EntityFactoryInterface
{
    /**
     * Create an entity from the given identity, entity data DTO, and audit data DTO.
     *
     * @param IdentityInterface $id
     * @param EntityCreationDataInterface $entityDto Business data DTO
     * @param AuditDataInterface $auditDto Audit metadata DTO
     * @return EntityInterface Created entity instance
     */
    public function createEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $entityDto,
        AuditDataInterface $auditDto
    ): EntityInterface;
}
