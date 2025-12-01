<?php
declare(strict_types=1);

namespace App\Application\Session\DTOs;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\UserId;

final class SessionCreationData implements EntityCreationDataInterface
{
    public function __construct(
        public readonly SessionId $sessionId,
        public readonly UserId $userId,
        public readonly SessionToken $accessToken,
        public readonly ?RefreshToken $refreshToken,
        public readonly ExpirationTime $expiresAt,
        public readonly IpAddress $createdIp,
        public readonly ?IpAddress $lastIpAddress = null,
        public readonly SessionStatus $status = SessionStatus::ACTIVE,
        public readonly ?CsrfToken $csrfToken = null,
        public readonly ?string $rawSessionData = null
    ) {}
}
