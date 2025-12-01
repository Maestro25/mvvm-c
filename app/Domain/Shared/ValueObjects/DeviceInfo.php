<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\DeviceInfoException;
use App\Domain\Shared\ValueObjects\ValueObject;

/**
 * Value Object representing device information.
 */
final class DeviceInfo extends ValueObject
{
    private string $deviceInfo;

    public function __construct(string $deviceInfo)
    {
        $deviceInfo = trim($deviceInfo);
        if ($deviceInfo === '') {
            throw DeviceInfoException::empty();
        }
        if (strlen($deviceInfo) > 256) {
            throw DeviceInfoException::tooLong(256);
        }
        $this->deviceInfo = $deviceInfo;
    }

    protected function getAtomicValues(): array
    {
        return [$this->deviceInfo];
    }

    public function __toString(): string
    {
        return $this->deviceInfo;
    }
}
