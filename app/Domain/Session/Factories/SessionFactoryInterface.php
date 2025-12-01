<?php
declare(strict_types=1);

namespace App\Domain\Session\Factories;

use App\Application\Session\DTOs\SessionCreationData;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Session\Entities\Session;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\Factories\EntityFactoryInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Session\Enums\SessionStatus;

interface SessionFactoryInterface extends EntityFactoryInterface
{
    /**
     * Create a transient (non-persisted) session entity for guest users.
     *
     * @param IdentityInterface $id
     * @param SessionToken $accessToken
     * @param RefreshToken|null $refreshToken
     * @param CsrfToken|null $csrfToken
     * @param ExpirationTime $expiresAt
     * @param IpAddress $createdIp
     * @param IpAddress|null $lastIpAddress
     * @param SessionStatus $status
     * @param string|null $rawSessionData
     * @return Session
     */
    public function createTransientSession(
        IdentityInterface $id,
        SessionToken $accessToken,
        ?RefreshToken $refreshToken,
        ?CsrfToken $csrfToken,
        ExpirationTime $expiresAt,
        IpAddress $createdIp,
        ?IpAddress $lastIpAddress = null,
        SessionStatus $status = SessionStatus::ACTIVE,
        ?string $rawSessionData = null
    ): Session;
}
