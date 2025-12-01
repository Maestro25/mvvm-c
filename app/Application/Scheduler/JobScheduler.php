<?php
declare(strict_types=1);

namespace App\Application\Scheduler;

use App\Domain\Job\Entities\JobInterface;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;
use Cron\CronExpression;

final class JobScheduler implements JobSchedulerInterface
{
    private array $registeredJobs = [];
    private array $scheduledJobs = [];

    public function __construct(private readonly LoggerInterface $logger) {}

    public function registerJob(string $jobKey, JobInterface $job, string $cronExpression): void
    {
        if (isset($this->registeredJobs[$jobKey])) {
            throw new \InvalidArgumentException("Job with key '{$jobKey}' is already registered.");
        }

        if (!CronExpression::isValidExpression($cronExpression)) {
            throw new \InvalidArgumentException("Invalid cron expression '{$cronExpression}'.");
        }

        $this->registeredJobs[$jobKey] = ['job' => $job, 'cron' => $cronExpression];
        $this->logger->info("JobScheduler: Registered job '{$jobKey}' with schedule '{$cronExpression}'.");
    }

    public function schedule(JobInterface $job): void
    {
        $this->scheduledJobs[] = $job;
        $this->logger->info("JobScheduler: Dynamically scheduled job '{$job->getId()}'.");
    }

    public function runDueJobs(?DateTimeImmutable $currentTime = null): void
    {
        $currentTime = $currentTime ?? new DateTimeImmutable();

        foreach ($this->registeredJobs as $key => ['job' => $job, 'cron' => $cron]) {
            if ($this->isDue($cron, $currentTime)) {
                $this->executeJob($key, $job);
            }
        }

        while ($job = array_shift($this->scheduledJobs)) {
            $this->executeJob('ad-hoc:' . $job->getId(), $job);
        }
    }

    private function executeJob(string $key, JobInterface $job): void
    {
        $this->logger->info("JobScheduler: Running job '{$key}'.");
        try {
            $job->execute($this->logger);
            $this->logger->info("JobScheduler: Job '{$key}' completed successfully.");
        } catch (\Throwable $e) {
            $this->logger->error("JobScheduler: Job '{$key}' failed with error: {$e->getMessage()}", [
                'exception' => $e,
                'jobKey' => $key,
            ]);
        }
    }

    private function isDue(string $cronExpression, DateTimeImmutable $time): bool
    {
        $cron = new CronExpression($cronExpression);
        return $cron->isDue($time);
    }

    public function getRegisteredJobs(): array
    {
        return $this->registeredJobs;
    }
}
