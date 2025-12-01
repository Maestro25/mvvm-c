<?php
declare(strict_types=1);

namespace App\Domain\Job\Factories;

use App\Application\Job\DTOs\JobData;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\DTOs\AuditDataInterface;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Job\Entities\SessionGcJob;
use App\Domain\Job\Enums\JobStatus;
use App\Domain\Job\ValueObjects\JobName;
use App\Domain\Shared\Factories\EntityFactory;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Application\Session\Services\SessionGarbageCollector;
use App\Domain\Job\Entities\Job;
use App\Domain\Shared\ValueObjects\JobId;

final class JobFactory extends EntityFactory implements JobFactoryInterface
{
    public function __construct(
        AuditInfoFactoryInterface $auditInfoFactory,
        private readonly SessionGarbageCollector $sessionGarbageCollector,
    ) {
        parent::__construct($auditInfoFactory);
    }

    /**
     * Factory method to create a Job entity with audit info.
     *
     * @param IdentityInterface $id
     * @param EntityCreationDataInterface $entityDto
     * @param AuditDataInterface $auditDto
     * @return JobInterface
     */
    public function createEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $entityDto,
        AuditDataInterface $auditDto
    ): JobInterface {
        $createdInfo = $this->auditInfoFactory->createForCreation($id, $auditDto);
        $updatedInfo = $this->auditInfoFactory->createForUpdate($id, $auditDto);
        $deletedInfo = null; // no deletion audit info during creation

        if (!$entityDto instanceof JobData) {
            throw new \InvalidArgumentException('Expected JobData DTO.');
        }

        return $this->instantiateEntity($id, $entityDto, $createdInfo, $updatedInfo, $deletedInfo);
    }

    protected function instantiateEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $dto,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo,
        ?AuditInfo $deletedInfo
    ): JobInterface {
        if (!$dto instanceof JobData) {
            throw new \InvalidArgumentException('Expected JobData DTO.');
        }

        return match ((string) $dto->name) {
            'Session Garbage Collection' => $this->instantiateSessionGcJob($id, $dto, $createdInfo, $updatedInfo),
            default => throw new \RuntimeException('Unsupported job type: ' . $dto->name),
        };
    }

    private function instantiateSessionGcJob(
        IdentityInterface $id,
        JobData $dto,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo
    ): SessionGcJob {
        return new SessionGcJob(
            $id,
            $dto->name,
            $dto->status,
            $dto->schedule,
            $dto->retryCount,
            $createdInfo,
            $updatedInfo,
            $this->sessionGarbageCollector
        );
    }

    /**
     * Returns static recurring job templates without IDs, audit info, or dynamic fields.
     *
     * @return array<int, array{jobData: JobData, cronExpression: string}>
     */
    public function getRecurringJobDefinitions(): array
    {
        return [
            [
                'jobData' => new JobData(
                    id: JobId::generate(),  // Placeholder, real ID assigned on creation
                    name: new JobName('Session Garbage Collection'),
                    status: JobStatus::WAITING,
                    schedule: new \DateTimeImmutable(),
                    retryCount: 0,
                    parameters: []
                ),
                'cronExpression' => '*/30 * * * *',  // Every 30 minutes
            ],
            // Additional recurring jobs can be defined here
        ];
    }
}
