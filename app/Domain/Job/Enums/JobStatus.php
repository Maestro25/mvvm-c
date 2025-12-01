<?php
declare(strict_types=1);

namespace App\Domain\Job\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing the lifecycle states of a Job with utility methods.
 */
enum JobStatus: string
{
    use EnumHelpers;

    case WAITING = 'waiting';       // Job is queued but not yet scheduled
    case SCHEDULED = 'scheduled';   // Job is scheduled to run at a given time
    case PROCESSING = 'processing'; // Job is currently running
    case COMPLETED = 'completed';   // Job finished successfully
    case FAILED = 'failed';         // Job execution failed
    case CANCELLED = 'cancelled';   // Job was cancelled and will not run

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'Waiting',
            self::SCHEDULED => 'Scheduled',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Check if the status represents a final terminal state.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED], true);
    }

    /**
     * Check if the job can be retried from the current status.
     */
    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }
}
