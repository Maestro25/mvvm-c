<?php
declare(strict_types=1);

namespace App\Application\Job\DTOs;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Job\Enums\JobStatus;
use App\Domain\Shared\ValueObjects\JobId;
use App\Domain\Job\ValueObjects\JobName;
use App\Domain\Shared\ValueObjects\AuditInfo;
use DateTimeImmutable;

final class JobData implements EntityCreationDataInterface
{
    public function __construct(
        public readonly JobId $id,
        public readonly JobName $name,
        public readonly JobStatus $status,
        public readonly DateTimeImmutable $schedule,
        public readonly array $parameters = [],
        public readonly int $retryCount = 0,
    ) {}
}
