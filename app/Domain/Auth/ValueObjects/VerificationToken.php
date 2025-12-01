<?php
declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use App\Domain\Auth\Exceptions\InvalidVerificationTokenException;
use App\Domain\Shared\ValueObjects\HashedToken;

final class VerificationToken extends HashedToken
{
    protected static function getTokenRegex(): string
    {
        return '/^[a-f0-9]{64}$/i';
    }

    public function __construct(string $token)
    {
        try {
            parent::__construct($token);
        } catch (\InvalidArgumentException $e) {
            throw InvalidVerificationTokenException::fromInvalidToken($token);
        }
    }
}
