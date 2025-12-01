<?php
declare(strict_types=1);

namespace App\Domain\Auth\ValueObjects;

use App\Domain\Shared\ValueObjects\JwtToken;
use App\Domain\Auth\Exceptions\InvalidAuthTokenException;

final class AuthToken extends JwtToken
{
    // No change; uses parent AbstractJwtToken.
}
