<?php
declare(strict_types=1);

namespace App\Application\Job\Services;

use App\Application\Job\DTOs\JobData;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Shared\ValueObjects\JobId;
use App\Domain\Job\Enums\JobStatus;
use App\Domain\Shared\ValueObjects\UserId;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * JobServiceInterface defines operations related to job lifecycle management.
 */
interface JobServiceInterface
{   public function getJobById(JobId $jobId): JobInterface;
    /**
     * Create and persist a new job from given DTO.
     *
     * @param JobData $jobData
     * @return JobId
     */
    public function createJob(JobData $jobData, ServerRequestInterface $request, ?UserId $currentUserId): JobId;

    /**
     * Schedule a job for execution by its ID.
     *
     * @param JobId $jobId
     */
    public function scheduleJob(JobId $jobId): void;

    /**
     * Execute a job immediately by its ID.
     *
     * @param JobId $jobId
     * @param LoggerInterface|null $logger Optional logger for execution output.
     */
    public function executeJob(JobId $jobId, ?LoggerInterface $logger = null): void;

    /**
     * Update the status of a job.
     *
     * @param JobId $jobId
     * @param JobStatus $status
     */
    public function updateJobStatus(JobId $jobId, JobStatus $status): void;

    /**
     * Retry a job respecting retry count and max retries.
     *
     * @param JobId $jobId
     */
    public function retryJob(JobId $jobId): void;
}
