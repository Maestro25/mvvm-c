<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidUsernameException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class Username extends ValueObject
{
    private readonly string $username;

    public function __construct(string $username)
    {
        $username = trim($username);
        $length = mb_strlen($username);

        if ($length < 3 || $length > 50) {
            throw InvalidUsernameException::lengthViolation($length);
        }

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{1,48}[a-zA-Z0-9]$/', $username)) {
            throw InvalidUsernameException::invalidFormat();
        }

        $this->username = $username;
    }

    protected function getAtomicValues(): array
    {
        return [$this->username];
    }

    public function __toString(): string
    {
        return $this->username;
    }
}
