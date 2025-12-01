<?php
declare(strict_types=1);

namespace App\Application\Scheduler;

use App\Domain\Job\Entities\JobInterface;
use DateTimeImmutable;

/**
 * JobSchedulerInterface defines contract for scheduling and executing jobs.
 */
interface JobSchedulerInterface
{
    /**
     * Register a recurring job with a cron schedule.
     *
     * @param string $jobKey Unique job identifier.
     * @param JobInterface $job The job instance.
     * @param string $cronExpression The schedule in cron format.
     *
     * @throws \InvalidArgumentException If jobKey already registered or invalid cron.
     */
    public function registerJob(string $jobKey, JobInterface $job, string $cronExpression): void;

    /**
     * Schedule an ad-hoc job for execution.
     *
     * @param JobInterface $job
     */
    public function schedule(JobInterface $job): void;

    /**
     * Run all recurring jobs due at the given time and all scheduled ad-hoc jobs.
     * 
     * @param DateTimeImmutable|null $currentTime Current time to check due jobs
     */
    public function runDueJobs(?DateTimeImmutable $currentTime = null): void;
}
