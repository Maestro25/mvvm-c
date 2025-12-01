<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Session\Repositories;

use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Session\Entities\Session;
use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Session\Repositories\SessionRepositoryInterface;
use App\Infrastructure\Persistence\Shared\Exceptions\PersistenceException;
use App\Infrastructure\Persistence\Shared\Repositories\Repository;
use App\Infrastructure\Persistence\Session\Mappers\SessionDbMapperInterface;
use App\Domain\Shared\Exceptions\EntityNotFoundException;
use SafeMySQL;

final class SessionRepository extends Repository implements SessionRepositoryInterface
{
    protected string $primaryKey = 'id';

    public function __construct(
        SafeMySQL $db,
        SessionDbMapperInterface $mapper,
    ) {
        parent::__construct($db, 'session', $mapper);
    }
    /**
     * @param IdentityInterface $id
     * @return SessionInterface
     */
    public function getById(IdentityInterface $id): SessionInterface
    {
        try {
            $row = $this->db->getRow(
                "SELECT * FROM ?n WHERE ?n = ?s LIMIT 1",
                $this->table,
                $this->primaryKey,
                (string) $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch session by id', $e);
        }

        if ($row === null) {
            throw EntityNotFoundException::forId((string) $id);
        }

        return $this->mapper->toEntity($row);
    }

    public function getByAccessToken(string $accessToken): ?SessionInterface
    {
        try {
            $row = $this->db->getRow(
                "SELECT * FROM ?n WHERE session_token = ?s LIMIT 1",
                $this->table,
                $accessToken
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch session by access token', $e);
        }

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function getByRefreshToken(string $refreshToken): ?SessionInterface
    {
        try {
            $row = $this->db->getRow(
                "SELECT * FROM ?n WHERE refresh_token = ?s LIMIT 1",
                $this->table,
                $refreshToken
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch session by refresh token', $e);
        }

        return $row ? $this->mapper->toEntity($row) : null;
    }

    public function save(EntityInterface $entity): bool
    {
        if (!$entity instanceof Session) {
            throw new \LogicException('Entity must be instance of Session');
        }

        $this->beginTransaction();

        try {
            $data = $this->mapper->toDbArray($entity);

            if (empty($data[$this->primaryKey])) {
                throw new \LogicException('Entity ID must be set before saving.');
            }

            // Use INSERT ... ON DUPLICATE KEY UPDATE with 'id' as primary key
            $id = $data[$this->primaryKey];
            unset($data[$this->primaryKey]);

            $this->db->query(
                "INSERT INTO ?n SET ?u ON DUPLICATE KEY UPDATE ?u",
                $this->table,
                array_merge([$this->primaryKey => $id], $data),
                $data
            );

            $this->commit();

            return true;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw PersistenceException::failure('save session entity', $e);
        }
    }

    /**
     * Marks session as revoked with optional actor and IP info.
     */
    public function revokeSession(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $ipBinary = null, ?string $reason = null): bool
    {
        $data = [
            'revoked_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.u'),
            'session_status' => 'revoked',
            'revocation_reason' => $reason,
        ];

        if ($actorId !== null) {
            $data['revoked_by'] = (string) $actorId;
        }

        if ($ipBinary !== null) {
            $data['revoked_ip'] = $ipBinary;
        }

        try {
            $result = $this->db->query(
                "UPDATE ?n SET ?u WHERE ?n = ?s",
                $this->table,
                $data,
                $this->primaryKey,
                (string) $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('revoke session', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('revoke session - no rows updated');
        }

        return true;
    }

    /**
     * Deprecated delete replaced with revokeSession for logical revocation.
     * Kept for backward compatibility but calls revokeSession internally.
     */
    public function delete(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $ipBinary = null): bool
    {
        return $this->revokeSession($id, $actorId, $ipBinary);
    }

    /**
     * Get active sessions based on user_id with expiration check and status.
     */
    public function getActiveSessionsByUser(IdentityInterface $userId): array
    {
        try {
            $rows = $this->db->getAll(
                "SELECT * FROM ?n WHERE user_id = ?s AND session_status = 'active' AND (expires_at IS NULL OR expires_at > NOW())",
                $this->table,
                (string) $userId
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch active sessions by user', $e);
        }

        return array_map(fn(array $row): EntityInterface => $this->mapper->toEntity($row), $rows);
    }

    public function deleteExpiredSessions(): int
    {
        try {
            return (int) $this->db->query(
                "DELETE FROM ?n WHERE expires_at <= NOW() OR (session_status = 'revoked' AND revoked_at <= DATE_SUB(NOW(), INTERVAL 30 DAY))",
                $this->table
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('delete expired sessions', $e);
        }
    }

    public function updateLastUsed(IdentityInterface $id, \DateTimeImmutable $lastUsedAt): bool
    {
        try {
            $result = $this->db->query(
                "UPDATE ?n SET last_used_at = ?s WHERE ?n = ?s",
                $this->table,
                $lastUsedAt->format('Y-m-d H:i:s.u'),
                $this->primaryKey,
                (string) $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('update last used session', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('update last used session - no rows updated');
        }

        return true;
    }

    public function searchSessions(array $criteria, int $limit, int $offset): array
    {
        $where = [];
        $bindings = [];

        if (isset($criteria['user_id'])) {
            $where[] = 'user_id = ?s';
            $bindings[] = (string) $criteria['user_id'];
        }

        if (isset($criteria['session_status'])) {
            $where[] = 'session_status = ?s';
            $bindings[] = $criteria['session_status'];
        }

        if (isset($criteria['created_after'])) {
            $where[] = 'created_at >= ?s';
            $bindings[] = $criteria['created_after']->format('Y-m-d H:i:s.u');
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            // Spread array for SafeMysql placeholders
            $params = array_merge([$this->table], $bindings, [$limit, $offset]);

            $rows = $this->db->getAll(
                "SELECT * FROM ?n $whereClause ORDER BY created_at DESC LIMIT ?i OFFSET ?i",
                ...$params
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('search sessions', $e);
        }

        return array_map(fn(array $row): EntityInterface => $this->mapper->toEntity($row), $rows);
    }
    public function updateMetadata(IdentityInterface $id, array $meta): bool
    {
        if (empty($meta)) {
            return false;
        }

        // Filter or validate allowed fields -- allowed keys aligned with DB schema
        $allowedFields = [
            'ip_address',
            'last_ip_address',
            'user_agent',
            'session_data',
            'updated_at',
            'updated_by',
            'updated_ip',
            'last_used_at',
            'expires_at',
            'revoked_at',
            'revoked_by',
            'revocation_reason',
            'is_ip_changed',
            'session_status',
            'version',
        ];
        $fieldsToUpdate = array_intersect_key($meta, array_flip($allowedFields));

        // Automatically set updated_at timestamp if not provided
        if (!array_key_exists('updated_at', $fieldsToUpdate)) {
            $fieldsToUpdate['updated_at'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s.u');
        }

        try {
            $result = $this->db->query(
                "UPDATE ?n SET ?u WHERE ?n = ?s",
                $this->table,
                $fieldsToUpdate,
                $this->primaryKey,
                $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('update session metadata', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('update session metadata - no rows updated');
        }

        return true;
    }

}
