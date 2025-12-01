<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidTimezoneException;
use DateTimeZone;

final class Timezone extends ValueObject
{
    private DateTimeZone $timezone;

    public function __construct(string $timezoneName)
    {
        try {
            $this->timezone = new DateTimeZone($timezoneName);
        } catch (\Exception $e) {
            throw InvalidTimezoneException::invalid($timezoneName);
        }
    }

    public function getDateTimeZone(): DateTimeZone
    {
        return $this->timezone;
    }

    protected function getAtomicValues(): array
    {
        return [$this->timezone->getName()];
    }

    public function __toString(): string
    {
        return $this->timezone->getName();
    }
}
