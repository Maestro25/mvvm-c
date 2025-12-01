<?php
declare(strict_types=1);

namespace App\Domain\Shared\Factories;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\DTOs\AuditDataInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;

abstract class EntityFactory implements EntityFactoryInterface
{


    public function __construct(protected readonly AuditInfoFactoryInterface $auditInfoFactory)
    {
    }

    public function createEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $entityDto,
        AuditDataInterface $auditDto
    ): EntityInterface {
        // Use audit factory to create AuditInfo domain value objects
        $createdInfo = $this->auditInfoFactory->createForCreation($id, $auditDto);
        $updatedInfo = $this->auditInfoFactory->createForUpdate($id, $auditDto);
        $deletedInfo = null; // pass null or create if deletion audit data present

        return $this->instantiateEntity(
            $id,
            $entityDto,
            $createdInfo,
            $updatedInfo,
            $deletedInfo
        );
    }

    /**
     * Concrete factories implement instantiation with audit info value objects
     *
     * @param IdentityInterface $id
     * @param EntityCreationDataInterface $entityDto
     * @param AuditInfo $createdInfo
     * @param AuditInfo $updatedInfo
     * @param AuditInfo|null $deletedInfo
     * @return EntityInterface
     */
    abstract protected function instantiateEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $entityDto,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo,
        ?AuditInfo $deletedInfo
    ): EntityInterface;
}
