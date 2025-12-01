<?php
declare(strict_types=1);

namespace App\Domain\Job\Entities;

use App\Domain\Job\Enums\JobStatus;
use App\Domain\Job\ValueObjects\JobName;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Application\Session\Services\SessionGarbageCollector;
use App\Domain\Shared\ValueObjects\JobId;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

final class SessionGcJob extends Job implements JobInterface
{
    public function __construct(
        private readonly IdentityInterface $id,
        private readonly JobName $name,
        private readonly JobStatus $status,
        private readonly DateTimeImmutable $schedule,
        private readonly int $retryCount,
        private readonly AuditInfo $createdInfo,
        private readonly AuditInfo $updatedInfo,
        private readonly SessionGarbageCollector $garbageCollector
    ) {
    }

    public function execute(?LoggerInterface $logger = null): void
    {
        $logger?->info("Starting Session Garbage Collection Job: " . $this->id);

        $deletedCount = $this->garbageCollector->collectGarbage();

        $logger?->info("Session Garbage Collection completed. Sessions deleted: " . $deletedCount);
    }

    public function getId(): JobId
    {
        return $this->id;
    }
    public function getDescription(): string
    {
        return 'PHP Session Garbage Collection Job';
    }
}
