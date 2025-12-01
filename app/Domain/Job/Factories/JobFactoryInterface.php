<?php
declare(strict_types=1);

namespace App\Domain\Job\Factories;

use App\Application\Job\DTOs\JobData;
use App\Application\Shared\DTOs\AuditCreationData;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Shared\Factories\EntityFactoryInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;

interface JobFactoryInterface extends EntityFactoryInterface
{
 
    /**
     * Provide recurring job definitions as pairs of job DTO and cron expression,
     * constructed using provided audit creation data.
     *
     * @param AuditCreationData $auditCreationData
     * @return array<int, array{jobData: JobData, cronExpression: string}>
     */
    public function getRecurringJobDefinitions(): array;
}
