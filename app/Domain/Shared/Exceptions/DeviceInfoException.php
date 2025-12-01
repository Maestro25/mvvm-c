<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

use InvalidArgumentException;

final class DeviceInfoException extends InvalidArgumentException
{
    public static function empty(): self
    {
        return new self('Device info cannot be empty.');
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("Device info is too long. Maximum length allowed is {$maxLength} characters.");
    }
}
