<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidNameException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class Name extends ValueObject
{
    private readonly string $firstName;
    private readonly string $lastName;

    public function __construct(string $firstName, string $lastName)
    {
        $firstName = trim($firstName);
        $lastName = trim($lastName);

        if (mb_strlen($firstName) < 1 || mb_strlen($firstName) > 100) {
            throw InvalidNameException::firstNameLengthViolation(mb_strlen($firstName));
        }
        if (mb_strlen($lastName) < 1 || mb_strlen($lastName) > 100) {
            throw InvalidNameException::lastNameLengthViolation(mb_strlen($lastName));
        }

        if (!preg_match('/^[a-zA-Z\s\'-]+$/u', $firstName)) {
            throw InvalidNameException::invalidFirstNameFormat();
        }
        if (!preg_match('/^[a-zA-Z\s\'-]+$/u', $lastName)) {
            throw InvalidNameException::invalidLastNameFormat();
        }

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    protected function getAtomicValues(): array
    {
        return [$this->firstName, $this->lastName];
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
