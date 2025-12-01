<?php
declare(strict_types=1);

namespace App\Application\Job\Services;

use App\Application\Job\DTOs\JobData;
use App\Domain\Shared\ValueObjects\JobId;
use App\Domain\Job\Factories\JobFactoryInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Shared\ValueObjects\UserId;
use App\Application\Shared\Services\AuditService;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;
use App\Domain\Job\Entities\JobInterface;

final class JobRegistry implements JobRegistryInterface
{
    /**
     * @var array<string, array{job: JobInterface, cron: string}>
     */
    private array $registeredJobs = [];

    public function __construct(
        private readonly JobFactoryInterface $jobFactory,
        private readonly LoggerInterface $logger,
        private readonly AuditService $auditService,
        private readonly AuditInfoFactoryInterface $auditInfoFactory
    ) {
    }

    /**
     * Registers recurring jobs internally.
     * Does not register to scheduler directly.
     * 
     * Accepts PSR-7 request and current UserId as parameters,
     * which are dynamic per execution context.
     */
    public function registerAllRecurringJobs(ServerRequestInterface $request, ?UserId $currentUserId): void
    {
        $auditDto = $this->auditService->createAuditData($request, $currentUserId);

        foreach ($this->jobFactory->getRecurringJobDefinitions() as $definition) {
            $jobId = JobId::generate();

            // Just pass plain JobData DTO and AuditInfo objects to factory
            $job = $this->jobFactory->createEntity($jobId, $definition['jobData'],  $auditDto);

            $this->registeredJobs[$definition['jobId'] ?? (string) $jobId] = [
                'job' => $job,
                'cron' => $definition['cronExpression'],
            ];

            $this->logger->info("Registered recurring job {$definition['jobId']} with cron {$definition['cronExpression']}");
        }
    }


    /**
     * Returns all registered recurring jobs.
     *
     * @return array<string, array{job: JobInterface, cron: string}>
     */
    public function getRegisteredJobs(): array
    {
        return $this->registeredJobs;
    }
}
