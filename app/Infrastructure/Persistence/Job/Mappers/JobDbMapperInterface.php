<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Job\Mappers;

use App\Application\Job\DTOs\JobData;
use App\Domain\Job\Entities\Job;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Infrastructure\Persistence\Shared\Mappers\DbMapperInterface;

interface JobDbMapperInterface extends DbMapperInterface
{
    /**
     * Converts a database row to a Job domain entity.
     *
     * @param array<string, mixed> $data
     * @return JobData
     */
    public function toDto(array $data): JobData;

    /**
     * Converts a Job domain entity to a database row array.
     *
     * @param Job|EntityInterface $entity
     * @return array<string, mixed>
     */
    public function toDbArray(EntityInterface $entity): array;
}
