<?php
declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use App\Domain\Auth\Exceptions\InvalidResetTokenException;
use App\Domain\Shared\ValueObjects\HashedToken;

final class ResetToken extends HashedToken
{
    protected static function getTokenRegex(): string
    {
        return '/^[a-f0-9]{64}$/';
    }

    public function __construct(string $token)
    {
        try {
            parent::__construct($token);
        } catch (\InvalidArgumentException $e) {
            throw InvalidResetTokenException::fromInvalidToken($token);
        }
    }
}
