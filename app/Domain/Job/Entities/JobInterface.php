<?php
declare(strict_types=1);

namespace App\Domain\Job\Entities;

use App\Domain\Job\Enums\JobStatus;
use App\Domain\Job\ValueObjects\JobName;
use App\Domain\Shared\Entities\EntityInterface;
use Psr\Log\LoggerInterface;

interface JobInterface extends EntityInterface
{
    /**
     * Executes the job logic.
     *
     * @param LoggerInterface|null $logger Optional logger for internal logging.
     */
    public function execute(?LoggerInterface $logger = null): void;

    /**
     * Returns a short human-readable job description.
     */
    public function getDescription(): string;

    /**
     * Returns the job's unique name or identity.
     */
    public function getName(): JobName;
    public function setStatus(JobStatus $status): void;
    /**
     * Returns the max number of retry attempts for this job.
     */
    public function getMaxRetries(): int;

    /**
     * Returns the current retry count.
     */
    public function getRetryCount(): int;

    /**
     * Increments the retry count.
     */
    public function incrementRetryCount(): void;
}
