<?php
declare(strict_types=1);

namespace App\Domain\Job\Entities;

use App\Domain\Job\Enums\JobStatus;
use App\Domain\Job\Validation\Traits\JobValidationGuard;
use App\Domain\Job\ValueObjects\JobName;
use App\Domain\Shared\Entities\Entity;
use App\Domain\Shared\ValueObjects\JobId;
use App\Domain\Shared\ValueObjects\AuditInfo;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

abstract class Job extends Entity implements JobInterface
{
    use JobValidationGuard;

    public function __construct(
        private readonly JobId $jobId,
        private readonly JobName $name,
        private readonly JobStatus $status,
        private readonly DateTimeImmutable $schedule,
        private readonly AuditInfo $createdInfo,
        private readonly AuditInfo $updatedInfo,
        private int $retryCount = 0,
        private int $maxRetries = 3 // Default max retries; override in subclasses if needed
    ) {
        parent::__construct($jobId);
        $this->validate();
    }

    abstract public function execute(?LoggerInterface $logger = null): void;

    public function getId(): JobId
    {
        return $this->jobId;
    }

    public function getName(): JobName
    {
        return $this->name;
    }

    public function getStatus(): JobStatus
    {
        return $this->status;
    }
    public function setStatus(JobStatus $status): void
    {
        // Optionally add validation here
        $this->status = $status; // Remove readonly to allow this
    }

    public function getSchedule(): DateTimeImmutable
    {
        return $this->schedule;
    }

    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    public function getUpdatedInfo(): AuditInfo
    {
        return $this->updatedInfo;
    }

    public function getRetryCount(): int
    {
        return $this->retryCount;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    public function incrementRetryCount(): void
    {
        $this->retryCount++;
    }
}
