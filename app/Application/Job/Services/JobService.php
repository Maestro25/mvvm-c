<?php
declare(strict_types=1);

namespace App\Application\Job\Services;

use App\Application\Job\DTOs\JobData;
use App\Application\Scheduler\JobSchedulerInterface;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Job\Enums\JobStatus;
use App\Domain\Job\Factories\JobFactoryInterface;
use App\Domain\Job\Repositories\JobRepositoryInterface;
use App\Domain\Shared\ValueObjects\JobId;
use App\Application\Shared\Services\AuditService;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;
use App\Domain\Shared\ValueObjects\UserId;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use DateTimeImmutable;

final class JobService implements JobServiceInterface
{
    public function __construct(
        private readonly JobRepositoryInterface $repository,
        private readonly JobSchedulerInterface $scheduler,
        private readonly JobFactoryInterface $jobFactory,
        private readonly AuditService $auditService,
        private readonly AuditInfoFactoryInterface $auditInfoFactory
    ) {}

    public function getJobById(JobId $jobId): JobInterface
    {
        return $this->repository->getById($jobId);
    }

    /**
     * Create a new Job entity with audit info.
     *
     * @param JobData $jobData Basic job data from DTO
     * @param ServerRequestInterface $request Current HTTP request (for audit context)
     * @param UserId|null $currentUserId Currently authenticated user ID or null
     * @return JobId
     */
    public function createJob(JobData $jobData, ServerRequestInterface $request, ?UserId $currentUserId): JobId
    {
        // Create audit context DTO for audit info creation
        $auditDto = $this->auditService->createAuditData($request, $currentUserId);

        // Generate JobId or use from DTO if available
        $jobId = $jobData->id ?? JobId::generate();

        // Create audit info instances needed by the factory
        // Create full domain entity with all needed parameters
        /** @var JobInterface $job */
        $job = $this->jobFactory->createEntity($jobId, $jobData,  $auditDto);

        $job->setStatus(JobStatus::WAITING);

        $this->repository->save($job);

        return $job->getId();
    }

    public function scheduleJob(JobId $jobId): void
    {
        $job = $this->repository->getById($jobId);
        if (!$job) {
            throw new \InvalidArgumentException("Job not found: " . $jobId);
        }

        $this->repository->updateJobStatus($jobId, JobStatus::SCHEDULED->value);
        $this->repository->updateJobScheduledTime($jobId, new DateTimeImmutable());

        $this->scheduler->schedule($job);
    }

    public function executeJob(JobId $jobId, ?LoggerInterface $logger = null): void
    {
        $job = $this->repository->getById($jobId);
        if (!$job) {
            throw new \InvalidArgumentException("Job not found: " . $jobId);
        }

        try {
            $this->repository->updateJobStatus($jobId, JobStatus::PROCESSING->value);

            $job->execute($logger);

            $this->repository->updateJobStatus($jobId, JobStatus::COMPLETED->value);
            $this->repository->updateJobExecutionTime($jobId, new DateTimeImmutable());
        } catch (\Throwable $e) {
            $this->repository->updateJobStatus($jobId, JobStatus::FAILED->value);

            if ($logger !== null) {
                $logger->error("Job execution failed: " . $e->getMessage(), ['exception' => $e]);
            }
            throw $e;
        }
    }

    public function updateJobStatus(JobId $jobId, JobStatus $status): void
    {
        $this->repository->updateJobStatus($jobId, $status->value);
    }

    public function retryJob(JobId $jobId): void
    {
        $job = $this->repository->getById($jobId);
        if (!$job) {
            throw new \InvalidArgumentException("Job not found: " . $jobId);
        }

        if ($job->getRetryCount() >= $job->getMaxRetries()) {
            throw new \RuntimeException("Exceeded max retries for job: " . $jobId);
        }

        $job->incrementRetryCount();
        $this->repository->save($job);
        $this->repository->updateJobRetryCount($jobId, $job->getRetryCount());

        $this->repository->updateJobStatus($jobId, JobStatus::WAITING->value);

        $this->scheduler->schedule($job);
    }
}
