<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\UserAgentException;
use App\Domain\Shared\ValueObjects\ValueObject;

/**
 * Value Object representing a User Agent string.
 */
final class UserAgent extends ValueObject
{
    private string $userAgent;

    public function __construct(string $userAgent)
    {
        $userAgent = trim($userAgent);
        if ($userAgent === '') {
            throw UserAgentException::empty();
        }
        if (strlen($userAgent) > 512) {
            throw UserAgentException::tooLong(512);
        }
        $this->userAgent = $userAgent;
    }

    protected function getAtomicValues(): array
    {
        return [$this->userAgent];
    }

    public function __toString(): string
    {
        return $this->userAgent;
    }
}
