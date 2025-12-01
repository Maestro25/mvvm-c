<?php
declare(strict_types=1);

namespace App\Domain\Shared\Exceptions;

final class IpAddressException extends ValueObjectException
{
    public static function invalidFormat(string $ip): self
    {
        return new self("Invalid IP address: $ip");
    }

    public static function invalidBinaryData(): self
    {
        return new self('Invalid binary IP data.');
    }

    public static function invalidBinaryConversion(): self
    {
        return new self('Invalid IP address for binary conversion.');
    }
}
