<?php
declare(strict_types=1);

namespace App\Domain\Session\ValueObjects;

use App\Domain\Session\Exceptions\CsrfTokenException;
use App\Domain\Shared\ValueObjects\JwtToken;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use DateTimeImmutable;

/**
 * CSRF tokens are opaque strings with expiration,
 * treated as JwtToken for expiration semantics.
 */
final class CsrfToken extends JwtToken
{
    public function __construct(string $token, ExpirationTime $expiry)
    {
        if (trim($token) === '') {
            throw CsrfTokenException::empty();
        }
        parent::__construct($token, $expiry);
    }
}
