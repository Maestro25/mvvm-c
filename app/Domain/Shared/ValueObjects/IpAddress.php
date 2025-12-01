<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\IpAddressException;
use App\Domain\Shared\ValueObjects\ValueObject;
use InvalidArgumentException;

/**
 * Value Object representing an IP Address (IPv4 or IPv6).
 */
final class IpAddress extends ValueObject
{
    private string $ip;

    public function __construct(string $ip)
    {
        $ip = trim($ip);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw IpAddressException::invalidFormat($ip);
        }
        $this->ip = $ip;
    }

    protected function getAtomicValues(): array
    {
        return [$this->ip];
    }

    public function __toString(): string
    {
        return $this->ip;
    }

    public static function fromBinary(string $binary): self
    {
        $ip = inet_ntop($binary);
        if ($ip === false) {
            throw IpAddressException::invalidBinaryData();
        }
        return new self($ip);
    }

    public function toBinary(): string
    {
        $binary = inet_pton($this->ip);
        if ($binary === false) {
            throw IpAddressException::invalidBinaryConversion();
        }
        return $binary;
    }
}
