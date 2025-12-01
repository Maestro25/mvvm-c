<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidEmailException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class Email extends ValueObject
{
    private readonly string $email;

    public function __construct(string $email)
    {
        $email = strtolower(trim($email));
        $sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::fromInvalidEmail($sanitizedEmail);
        }

        $this->email = $sanitizedEmail;
    }

    protected function getAtomicValues(): array
    {
        return [$this->email];
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
