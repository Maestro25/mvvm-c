<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidPasswordHashException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class PasswordHash extends ValueObject
{
    private const VALID_PREFIXES = [
        '$2y$', '$2a$', '$2b$', '$argon2i$', '$argon2id$'
    ];

    private readonly string $hash;

    public function __construct(string $hash)
    {
        $hash = trim($hash);

        if (!$this->isValidHash($hash)) {
            throw new InvalidPasswordHashException('Invalid password hash format');
        }

        $this->hash = $hash;
    }

    public static function fromPlainPassword(string $password): self
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash === false) {
            throw new \RuntimeException('Failed to hash password.');
        }
        return new self($hash);
    }

    private function isValidHash(string $hash): bool
    {
        foreach (self::VALID_PREFIXES as $prefix) {
            if (str_starts_with($hash, $prefix)) {
                return strlen($hash) >= 60;
            }
        }
        return false;
    }

    protected function getAtomicValues(): array
    {
        return [$this->hash];
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
