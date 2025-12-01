<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Job\Mappers;

use App\Application\Job\DTOs\JobData;
use App\Domain\Job\Entities\Job;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Job\Enums\JobStatus;
use App\Domain\Shared\ValueObjects\JobId;
use App\Domain\Job\ValueObjects\JobName;
use App\Domain\Shared\Entities\EntityInterface;
use App\Infrastructure\Persistence\Shared\Mappers\DbMapper;
use DateTimeImmutable;
use InvalidArgumentException;

final class JobDbMapper extends DbMapper implements JobDbMapperInterface
{
    /**
     * Empty for compatibility: entity creation done by a factory or service
     * @param array<string, mixed> $data
     * @return JobInterface
     */
    public function toEntity(array $data): JobInterface
    {
        throw new \LogicException('Use factory or domain service to create Job entities.');
    }

    /**
     * @param array<string, mixed> $data
     * @return JobData
     */
    public function toDto(array $data): JobData
    {
        return new JobData(
            id: new JobId($data['id']),
            name: new JobName($data['job_name']),
            status: JobStatus::from($data['status']),
            schedule: isset($data['scheduled_at']) ? new DateTimeImmutable($data['scheduled_at']) : null,
            retryCount: $data['retries'] ?? 0
        );
    }

    /**
     * Maps full entity (including audit info) to DB array
     * @param EntityInterface $entity
     * @return array<string, mixed>
     */
    public function toDbArray(EntityInterface $entity): array
    {
        if (!$entity instanceof Job) {
            throw new InvalidArgumentException('Entity must implement JobInterface');
        }

        return [
            'id' => (string)$entity->getId(),
            'job_name' => (string)$entity->getName(),
            'payload' => json_encode([]), // Adjust as needed
            'status' => $entity->getStatus()->value,
            'retries' => $entity->getRetryCount(),
            'scheduled_at' => $entity->getSchedule()?->format('Y-m-d H:i:s.u'),
            'created_at' => $entity->getCreatedInfo()->createdAt->format('Y-m-d H:i:s.u'),
            'created_by' => $entity->getCreatedInfo()->createdBy ?? null,
            'created_ip' => $entity->getCreatedInfo()->createdIp ?? null,
            'updated_at' => $entity->getUpdatedInfo()?->updatedAt->format('Y-m-d H:i:s.u'),
            'updated_by' => $entity->getUpdatedInfo()?->updatedBy ?? null,
            'updated_ip' => $entity->getUpdatedInfo()?->updatedIp ?? null,
        ];
    }
}
