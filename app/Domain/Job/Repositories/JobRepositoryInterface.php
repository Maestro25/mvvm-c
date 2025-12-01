<?php
declare(strict_types=1);

namespace App\Domain\Job\Repositories;

use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Job\Entities\JobInterface;
use DateTimeImmutable;

interface JobRepositoryInterface
{
    public function getById(IdentityInterface $id): JobInterface;

    public function save(EntityInterface $entity): bool;

    /**
     * @return JobInterface[]
     */
    public function getByStatus(string $status, int $limit = 50, int $offset = 0): array;

    /**
     * @return JobInterface[]
     */
    public function getScheduledJobsBefore(DateTimeImmutable $before, string $status = 'waiting'): array;

    public function updateJobStatus(
        IdentityInterface $id,
        string $status,
        ?int $retries = null,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $completedAt = null
    ): bool;

    public function updateJobScheduledTime(IdentityInterface $id, DateTimeImmutable $scheduledAt): bool;

    public function updateJobExecutionTime(IdentityInterface $id, DateTimeImmutable $completedAt): bool;

    public function updateJobRetryCount(IdentityInterface $id, int $retryCount): bool;

    public function deleteOldJobs(DateTimeImmutable $olderThan): int;

    /**
     * Search jobs with criteria
     * @return JobInterface[]
     */
    public function search(array $criteria, int $limit = 50, int $offset = 0): array;
}
