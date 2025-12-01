<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidPhoneException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class Phone extends ValueObject
{
    private readonly string $phone;

    public function __construct(string $phone)
    {
        $phone = trim($phone);

        if (!preg_match('/^\+?[0-9\s\-]{7,25}$/', $phone)) {
            throw InvalidPhoneException::invalidFormat($phone);
        }

        $this->phone = $phone;
    }

    protected function getAtomicValues(): array
    {
        return [$this->phone];
    }

    public function __toString(): string
    {
        return $this->phone;
    }
}
