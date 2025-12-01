<?php
declare(strict_types=1);

namespace App\Domain\Session\Factories;

use App\Application\Session\DTOs\SessionCreationData;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\DTOs\AuditDataInterface;
use App\Domain\Session\Entities\Session;
use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Shared\Factories\EntityFactory;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final class SessionFactory extends EntityFactory implements SessionFactoryInterface
{
    public function __construct(AuditInfoFactoryInterface $auditInfoFactory)
    {
        parent::__construct($auditInfoFactory);
    }

    protected function instantiateEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $dto,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo,
        ?AuditInfo $deletedInfo
    ): Session {
        if (!$dto instanceof SessionCreationData) {
            throw new \InvalidArgumentException('Expected SessionCreationData');
        }

        return new Session(
            sessionId: $id,
            userId: $dto->userId,
            accessToken: $dto->accessToken,
            refreshToken: $dto->refreshToken,
            createdInfo: $createdInfo,
            expiresAt: $dto->expiresAt,
            createdIp: $dto->createdIp,
            lastIpAddress: $dto->lastIpAddress,
            updatedInfo: $updatedInfo,
            status: $dto->status,
            csrfToken: $dto->csrfToken,
            rawSessionData: $dto->rawSessionData
        );
    }

    /**
     * Create a transient, non-persisted Session entity for guest users.
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
        ?string $rawSessionData = null,
    ): Session {
        $guestUserId = new UserId('00000000-0000-0000-0000-000000000000');

        $now = new \DateTimeImmutable();

        $createdInfo = new AuditInfo(
            createdAt: $now,
            createdBy: $guestUserId,
            createdIp: $createdIp,
            userAgent: null,
            deviceInfo: null
        );

        $updatedInfo = new AuditInfo(
            updatedAt: $now,
            updatedBy: $guestUserId,
            updatedIp: $createdIp,
            userAgent: null,
            deviceInfo: null
        );

        $dto = new SessionCreationData(
            sessionId: $id,
            userId: $guestUserId,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: $expiresAt,
            createdIp: $createdIp,
            lastIpAddress: $lastIpAddress,
            status: $status,
            csrfToken: $csrfToken,
            rawSessionData: $rawSessionData
        );

        return $this->instantiateEntity($id, $dto, $createdInfo, $updatedInfo, null);
    }

}
