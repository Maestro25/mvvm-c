<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Shared\ValueObjects\ExpirationTime;

interface TokenGeneratorInterface
{
    public function generateSessionToken(ExpirationTime $expiry): SessionToken;

    public function generateRefreshToken(ExpirationTime $expiry): RefreshToken;

    public function generateCsrfToken(ExpirationTime $expiry): CsrfToken;
}
