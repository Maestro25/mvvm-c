<?php
declare(strict_types=1);

namespace App\Domain\Job\Validation\Traits;

use App\Domain\Shared\Validation\Rules\NotEmptyRule;
use App\Domain\Shared\Validation\Rules\PatternRule;
use App\Domain\Shared\Validation\Rules\EnumInstanceRule;
use App\Domain\Shared\Validation\Rules\DateTimeImmutableRule;
use App\Domain\Shared\Validation\Rules\BooleanRule;
use App\Domain\Shared\Validation\Traits\ValidationGuard;
use App\Domain\Job\Enums\JobStatus;

trait JobValidationGuard
{
    use ValidationGuard;

    protected function getValidationRules(): array
    {
        return [
            'id' => new NotEmptyRule('Job ID must not be empty.'),
            'name' => new NotEmptyRule('Job name must not be empty.'),
            'status' => new EnumInstanceRule(JobStatus::class, 'Job status invalid.'),
            'schedule' => new PatternRule(
                '/^(\*|([0-5]?\d)) (\*|([0-5]?\d)) (\*|1?\d|2[0-3]) (\*|[1-9]|[12]\d|3[01]) (\*|[1-9]|1[0-2])$/',
                'Schedule must be a valid cron expression.'
            ),
            'retryCount' => new BooleanRule('Retry count must be a boolean.'), // adjust if retryCount is int
            'createdAt' => new DateTimeImmutableRule('Created at must be a valid DateTimeImmutable.'),
            'updatedAt' => new DateTimeImmutableRule('Updated at must be a valid DateTimeImmutable.', true), // nullable
        ];
    }

    protected function getValidationData(): array
    {
        return [
            'id' => (string) $this->getId(),
            'name' => (string) $this->getName(),
            'status' => $this->status ?? null,
            'schedule' => $this->schedule ?? null,
            'retryCount' => $this->retryCount ?? null,
            'createdAt' => $this->createdAt ?? null,
            'updatedAt' => $this->updatedAt ?? null,
        ];
    }

    // Implement getId() and getName() or expect them in using class
}
