<?php
declare(strict_types=1);

namespace App\Domain\Session\ValueObjects;

use App\Domain\Shared\ValueObjects\JwtToken;
use DateTimeImmutable;

final class SessionToken extends JwtToken
{
    // No change to constructor or methods; inherits all from AbstractJwtToken.
}
