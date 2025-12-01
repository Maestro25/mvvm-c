<?php
declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use App\Domain\Auth\Exceptions\InvalidRememberMeTokenException;
use App\Domain\Shared\ValueObjects\HashedToken;

final class RememberToken extends HashedToken
{
    protected static function getTokenRegex(): string
    {
        // Assuming a 64-character hex token
        return '/^[a-f0-9]{64}$/i';
    }

    public function __construct(string $token)
    {
        try {
            parent::__construct($token);
        } catch (\InvalidArgumentException $e) {
            throw InvalidRememberMeTokenException::fromInvalidToken($token);
        }
    }
}
