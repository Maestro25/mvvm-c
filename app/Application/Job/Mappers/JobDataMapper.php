<?php
declare(strict_types=1);

namespace App\Application\Job\Mappers;

use App\Application\Shared\Mappers\DtoMapper;
use App\Application\Job\DTOs\JobData;
use App\Domain\Job\Entities\Job;
use App\Domain\Shared\Entities\EntityInterface;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Job\Factories\JobFactoryInterface;

final class JobDataMapper extends DtoMapper implements JobDataMapperInterface
{
    private JobFactoryInterface $jobFactory;

    public function __construct(JobFactoryInterface $jobFactory)
    {
        $this->jobFactory = $jobFactory;
    }

    public function toDTO(EntityInterface $entity): EntityCreationDataInterface
    {
        if (!$entity instanceof Job) {
            throw new \InvalidArgumentException('Entity must be instance of Job');
        }

        return new JobData(
            id: $entity->getId(),
            name: $entity->getName(),
            status: $entity->getStatus(),
            schedule: $entity->getSchedule(),
            parameters: [], // add parameter extraction logic if applicable
            retryCount: $entity->getRetryCount(),
        );
    }

    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): EntityInterface
    {
        throw new \BadMethodCallException('toEntity() is not supported. Use factory directly for entity creation.');
    }

}
