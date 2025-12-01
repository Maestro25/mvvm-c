<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\JwtTokenException;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use InvalidArgumentException;

/**
 * Base class for immutable JWT tokens with expiration and signature validation.
 */
abstract class JwtToken extends ValueObject
{
    protected readonly string $jwt;
    protected readonly ExpirationTime $expiry;

    public function __construct(string $jwt, ExpirationTime $expiry)
    {
        if ($jwt === '') {
            throw JwtTokenException::emptyToken();
        }

        $this->jwt = $jwt;
        $this->expiry = $expiry;
    }

    public function __toString(): string
    {
        return $this->jwt;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiry->getExpiresAt();
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        return $this->expiry->isExpired($now);
    }

    /**
     * Validate JWT signature using given secret and algorithm.
     */
    public function validateSignature(string $secret, string $algorithm = 'HS256'): bool
    {
        try {
            JWT::decode($this->jwt, new Key($secret, $algorithm));
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function equals(mixed $other): bool
    {
        return $other instanceof static && $this->jwt === $other->jwt;
    }

    protected function getAtomicValues(): array
    {
        return [$this->jwt, (string) $this->expiry];
    }
    public function getToken(): string
    {
        return $this->jwt;
    }

}
