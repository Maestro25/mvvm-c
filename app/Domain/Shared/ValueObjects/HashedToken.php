<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\HashedTokenException;
use App\Domain\Shared\ValueObjects\ValueObject;
use InvalidArgumentException;

/**
 * Base class for immutable, hex-hashed tokens with regex format validation.
 */
abstract class HashedToken extends ValueObject
{
    protected readonly string $token;
    protected readonly string $tokenHash;

    abstract protected static function getTokenRegex(): string;

    public function __construct(string $token)
    {
        $normalized = strtolower(trim($token));
        if (!preg_match(static::getTokenRegex(), $normalized)) {
            throw HashedTokenException::invalidFormat($token);
        }

        $this->token = $normalized;
        $this->tokenHash = $this->hashToken($normalized);
    }

    protected function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    /**
     * Equality supports both token strings and HashedToken instances.
     */
    public function equals(mixed $other): bool
    {
        if (is_string($other)) {
            $other = strtolower(trim($other));
            if (!preg_match(static::getTokenRegex(), $other)) {
                return false;
            }
            return hash_equals($this->tokenHash, $this->hashToken($other));
        }

        if (!$other instanceof static) {
            return false;
        }

        return hash_equals($this->tokenHash, $other->getTokenHash());
    }

    public function __toString(): string
    {
        return $this->token;
    }

    protected function getAtomicValues(): array
    {
        return [$this->tokenHash];
    }
}
