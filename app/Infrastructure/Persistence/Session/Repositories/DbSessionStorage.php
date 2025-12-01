<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Repositories;

use App\Domain\Session\Repositories\DbSessionStorageInterface;
use App\Infrastructure\Persistence\Session\Mappers\SessionDbMapperInterface;
use App\Application\Session\Mappers\SessionCreationDataMapperInterface;
use App\Domain\Session\Factories\SessionFactoryInterface;
use App\Application\Session\DTOs\SessionCreationData;
use App\Application\Session\Services\TokenGenerator;
use App\Application\Shared\DTOs\AuditCreationData;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Session\Entities\Session;
use App\Domain\Session\Enums\SessionStatus;
use App\Domain\Shared\Factories\AuditInfoFactory;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\ExpirationTime;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\UserAgent;
use Psr\Log\LoggerInterface;
use SafeMySQL;


final class DbSessionStorage implements DbSessionStorageInterface
{

    public function __construct(
        private SafeMySQL $db,
        private LoggerInterface $logger,
        private SessionDbMapperInterface $mapper,
        private SessionCreationDataMapperInterface $dtoMapper,
        private SessionFactoryInterface $factory,
        private AuditInfoFactory $auditInfoFactory,
        private TokenGenerator $tokenGenerator,
        private string $table = 'session'
    ) {

    }

    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionId): string
    {
        $this->logger->info('Reading session data from DB', ['session_id' => $sessionId]);

        $row = $this->db->getRow(
            "SELECT * FROM ?n WHERE id = ?s AND (expires_at IS NULL OR expires_at > NOW(6)) AND session_status = 'active'",
            $this->table,
            $sessionId
        );

        if (!$row) {
            $this->logger->warning('No active session found in DB for session ID', ['session_id' => $sessionId]);
            return '';
        }

        /** @var Session $session */
        $session = $this->mapper->toEntity($row);
        $rawData = $session->getRawSessionData();

        if ($rawData === null) {
            $this->logger->warning('Session data is null for session ID', ['session_id' => $sessionId]);
            return '';
        }

        $this->logger->info('Session data successfully read from DB', [
            'session_id' => $sessionId,
            'session_data_length' => strlen($rawData),
            'session_data_snippet' => substr($rawData, 0, 100)
        ]);

        return $rawData;
    }

    public function write(string $sessionId, string $data, ?string $userId = null, array $meta = []): bool
    {
        // Log entry to confirm write() invocation and key data context
        $this->logger->info('Session write() called', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'data_length' => strlen($data),
            'data_snippet' => substr($data, 0, 100)
        ]);

        $now = new \DateTimeImmutable();

        $auditCreationData = new AuditCreationData(
            userId: $userId ? new UserId($userId) : null,
            ipAddress: isset($meta['ip_address']) ? new IpAddress($meta['ip_address']) : null,
            timezone: null,
            userAgent: isset($meta['user_agent']) ? new UserAgent($meta['user_agent']) : null,
            deviceInfo: null,
            timestamp: $now
        );

        $createdInfo = $this->auditInfoFactory->createForCreation(new SessionId($sessionId), $auditCreationData);

        $accessToken = $this->tokenGenerator->generateSessionToken(new ExpirationTime($now->modify('+1 hour')));
        $refreshToken = $this->tokenGenerator->generateRefreshToken(new ExpirationTime($now->modify('+30 days')));
        $csrfToken = $this->tokenGenerator->generateCsrfToken(new ExpirationTime($now->modify('+1 hour')));
        $expiresAt = new ExpirationTime($now->modify('+1 hour'));
        $createdIp = isset($meta['ip_address']) ? new IpAddress($meta['ip_address']) : null;

        $dto = new SessionCreationData(
            sessionId: new SessionId($sessionId),
            userId: $userId ? new UserId($userId) : new UserId(''),
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: $expiresAt,
            createdIp: $createdIp,
            lastIpAddress: null,
            status: SessionStatus::ACTIVE,
            csrfToken: $csrfToken,
            rawSessionData: $data
        );

        $session = $this->dtoMapper->toEntity($dto->sessionId, $dto);
        $dbArray = $this->mapper->toDbArray($session);

        $this->logger->info('Writing session data', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'client_ip' => $meta['ip_address'] ?? 'unknown',
            'user_agent' => $meta['user_agent'] ?? 'unknown',
            'session_data_length' => strlen($data),
            'session_data_snippet' => substr($data, 0, 100)
        ]);

        $result = $this->db->query(
            "INSERT INTO ?n SET ?u ON DUPLICATE KEY UPDATE ?u",
            $this->table,
            $dbArray,
            $dbArray
        );

        if ($result === false) {
            $this->logger->error('Failed to write session to DB', [
                'session_id' => $sessionId,
                'message' => 'SafeMySQL query returned false, DB error details not available from SafeMySQL',
            ]);
            return false;
        }

        return true;
    }





    public function destroy(string $sessionId): bool
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');

        $this->db->query(
            "UPDATE ?n SET session_status = 'revoked', revoked_at = ?s WHERE id = ?s AND session_status = 'active'",
            $this->table,
            $now,
            $sessionId
        );
        return true;
    }

    public function gc(int $maxLifetime): bool
    {
        $this->db->query(
            "UPDATE ?n SET session_status = 'expired', expires_at = NOW(6) WHERE expires_at <= NOW(6) AND session_status = 'active'",
            $this->table
        );
        return true;
    }

    public function findActiveSessionsByUser(string $userId): array
    {
        $rows = $this->db->getAll(
            "SELECT * FROM ?n WHERE user_id = ?s AND session_status = 'active'",
            $this->table,
            $userId
        );

        $sessions = [];
        foreach ($rows as $row) {
            $sessions[] = $this->mapper->toEntity($row);
        }
        return $sessions;
    }

    public function revokeSessionsByUser(string $userId, ?string $revocationReason = null): int
    {
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');
        $revocationReason = $revocationReason ?? 'Revoked by user request';

        $this->db->query(
            "UPDATE ?n SET session_status = 'revoked', revoked_at = ?s, revocation_reason = ?s WHERE user_id = ?s AND session_status = 'active'",
            $this->table,
            $now,
            $revocationReason,
            $userId
        );

        return $this->db->affectedRows();
    }

    public function updateMetadata(string $sessionId, array $meta): bool
    {
        $fields = [];
        if (isset($meta['ip_address'])) {
            $fields['ip_address'] = $meta['ip_address'];
        }
        if (isset($meta['user_agent'])) {
            $fields['user_agent'] = $meta['user_agent'];
        }
        if (isset($meta['last_used_at'])) {
            $fields['last_used_at'] = $meta['last_used_at'];
        } else {
            $fields['last_used_at'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');
        }

        if (empty($fields)) {
            return false;
        }

        $this->db->query(
            "UPDATE ?n SET ?u WHERE id = ?s",
            $this->table,
            $fields,
            $sessionId
        );

        return true;
    }
}
