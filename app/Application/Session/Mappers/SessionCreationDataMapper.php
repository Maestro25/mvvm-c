<?php
declare(strict_types=1);

namespace App\Application\Session\Mappers;

use App\Application\Shared\Mappers\DtoMapper;
use App\Application\Session\DTOs\SessionCreationData;
use App\Domain\Session\Entities\Session;
use App\Domain\Shared\Entities\EntityInterface;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Session\Factories\SessionFactoryInterface;

final class SessionCreationDataMapper extends DtoMapper implements SessionCreationDataMapperInterface
{
    public function toDTO(EntityInterface $entity): SessionCreationData
    {
        if (!$entity instanceof Session) {
            throw new \InvalidArgumentException('Entity must be instance of Session');
        }

        return new SessionCreationData(
            sessionId: $entity->getId(),
            userId: $entity->getUserId(),
            accessToken: $entity->getAccessToken(),
            refreshToken: $entity->getRefreshToken(),
            expiresAt: $entity->getExpiresAt(),
            createdIp: $entity->getCreatedIp(),
            lastIpAddress: $entity->getLastIpAddress(),
            status: $entity->getStatus(),
            csrfToken: $entity->getCsrfToken(),
            rawSessionData: $entity->getRawSessionData()
        );
    }

    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): Session
    {
        if (!$dto instanceof SessionCreationData) {
            throw new \InvalidArgumentException('DTO must be instance of SessionCreationData');
        }

        // Map DTO fields to domain entity directly without audit info
        return new Session(
            $id,
            $dto->userId,
            $dto->accessToken,
            $dto->refreshToken,
            null,
            $dto->expiresAt,
            $dto->createdIp,
            $dto->lastIpAddress,
            null,
            $dto->csrfToken,
            $dto->rawSessionData,
            $dto->status,
        );
    }
}

