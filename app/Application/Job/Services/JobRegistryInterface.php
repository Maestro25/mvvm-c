<?php
declare(strict_types=1);

namespace App\Application\Job\Services;

use App\Domain\Job\Entities\JobInterface;
use App\Domain\Shared\ValueObjects\UserId;
use Psr\Http\Message\ServerRequestInterface;

/**
 * JobRegistryInterface defines a contract for managing recurring job registrations.
 */
interface JobRegistryInterface
{
    /**
     * Register or define all recurring jobs internally.
     */
    public function registerAllRecurringJobs(ServerRequestInterface $request, ?UserId $currentUserId): void;

    /**
     * Return all registered recurring jobs with their cron schedules.
     *
     * @return array<string, array{job: JobInterface, cron: string}>
     */
    public function getRegisteredJobs(): array;
}
