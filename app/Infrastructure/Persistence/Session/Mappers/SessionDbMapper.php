<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Mappers;

use App\Infrastructure\Persistence\Shared\Mappers\DbMapper;
use App\Domain\Session\Entities\Session;
use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Session\ValueObjects\SessionToken;
use App\Domain\Session\ValueObjects\RefreshToken;
use App\Domain\Session\ValueObjects\CsrfToken;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\Entities\EntityInterface;

final class SessionDbMapper extends DbMapper implements SessionDbMapperInterface
{
    /**
     * @param array<string,mixed> $data DB row
     * @return Session
     */
    public function toEntity(array $data): Session
    {
        $sessionId = new SessionId($data['session_id']);
        $userId = new UserId($data['user_id']);
        $accessToken = new SessionToken($data['access_token'], new ExpirationTime(new \DateTimeImmutable($data['access_token_expires_at'])));
        $refreshToken = isset($data['refresh_token'])
            ? new RefreshToken($data['refresh_token'], new ExpirationTime(new \DateTimeImmutable($data['refresh_token_expires_at'])))
            : null;
        $csrfToken = isset($data['csrf_token'])
            ? new CsrfToken($data['csrf_token'], new ExpirationTime(new \DateTimeImmutable($data['csrf_token_expires_at'])))
            : null;

        $createdInfo = new AuditInfo(
            new \DateTimeImmutable($data['created_at']),
            isset($data['created_by']) ? new UserId($data['created_by']) : null,
            $data['created_ip'] ?? null
        );

        $updatedInfo = isset($data['updated_at'])
            ? new AuditInfo(
                new \DateTimeImmutable($data['updated_at']),
                isset($data['updated_by']) ? new UserId($data['updated_by']) : null,
                $data['updated_ip'] ?? null
            )
            : null;

        $expiresAt = new ExpirationTime(new \DateTimeImmutable($data['expires_at']));
        $createdIp = new IpAddress($data['created_ip']);
        $lastIpAddress = isset($data['last_ip_address']) ? new IpAddress($data['last_ip_address']) : null;

        // Map string status or fallback to ACTIVE if not set
        $status = isset($data['status']) && SessionStatus::tryFrom($data['status']) !== null
            ? SessionStatus::from($data['status'])
            : ((bool) $data['is_revoked'] ? SessionStatus::REVOKED : SessionStatus::ACTIVE);

        return new Session(
            $sessionId,
            $userId,
            $accessToken,
            $refreshToken,
            $createdInfo,
            $expiresAt,
            $createdIp,
            $lastIpAddress,
            $updatedInfo,
            $csrfToken,
            $data['raw_session_data'] ?? null,
            $status,
        );
    }

    /**
     * @param Session|EntityInterface $entity
     * @return array<string,mixed>
     */
    public function toDbArray(EntityInterface $entity): array
    {
        if (!$entity instanceof Session) {
            throw new \InvalidArgumentException('Entity must be instance of Session');
        }

        return [
            'session_id' => (string) $entity->getId(),
            'user_id' => (string) $entity->getUserId(),
            'access_token' => (string) $entity->getAccessToken(),
            'access_token_expires_at' => $entity->getAccessToken()->getExpiresAt()->format('Y-m-d H:i:s'),
            'refresh_token' => $entity->getRefreshToken() ? (string) $entity->getRefreshToken() : null,
            'refresh_token_expires_at' => $entity->getRefreshToken() ? $entity->getRefreshToken()->getExpiresAt()->format('Y-m-d H:i:s') : null,
            'csrf_token' => $entity->getCsrfToken() ? (string) $entity->getCsrfToken() : null,
            'csrf_token_expires_at' => $entity->getCsrfToken() ? $entity->getCsrfToken()->getExpiresAt()->format('Y-m-d H:i:s') : null,
            'created_at' => $entity->getCreatedInfo()->createdAt->format('Y-m-d H:i:s'),
            'created_by' => $entity->getCreatedInfo()->createdBy ? (string) $entity->getCreatedInfo()->createdBy : null,
            'created_ip' => $entity->getCreatedInfo()->createdIp,
            'updated_at' => $entity->getUpdatedInfo() ? $entity->getUpdatedInfo()->updatedAt->format('Y-m-d H:i:s') : null,
            'updated_by' => $entity->getUpdatedInfo() && $entity->getUpdatedInfo()->updatedBy ? (string) $entity->getUpdatedInfo()->updatedBy : null,
            'updated_ip' => $entity->getUpdatedInfo() ? $entity->getUpdatedInfo()->updatedIp : null,
            'expires_at' => $entity->getExpiresAt()->getExpiresAt()->format('Y-m-d H:i:s'),
            'last_ip_address' => $entity->getLastIpAddress() ? (string) $entity->getLastIpAddress() : null,
            'status' => $entity->getStatus()->value,  // Persist enum value as string
            'raw_session_data' => $entity->getRawSessionData(),
        ];
    }
}
