<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Repositories;

use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\User\Entities\User;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\Exceptions\EntityNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Shared\Exceptions\PersistenceException;
use App\Infrastructure\Persistence\Shared\Repositories\Repository;
use App\Infrastructure\Persistence\User\Mappers\UserDbMapperInterface;

/**
 * UserRepository implements persistence for User aggregate,
 * including profile and roles, soft delete, full save, and profile retrieval.
 */
final class UserRepository extends Repository implements UserRepositoryInterface
{
    protected string $primaryKey = 'id';

    public function __construct(
        \SafeMysql $db,
        UserDbMapperInterface $mapper,
    ) {
        parent::__construct($db, 'user', $mapper);
    }

    /**
     * Fetch user with profile and roles joined, excluding soft-deleted users and profiles.
     * @throws EntityNotFoundException|PersistenceException
     */
    public function getById(IdentityInterface $id): User
{
    try {
        $row = $this->db->getRow(
            "SELECT 
                u.*, 
                p.first_name, p.last_name, p.phone, p.address_line1, p.address_line2, p.city, p.state, p.postal_code, p.country, p.profile_picture, p.preferences, p.gender,
                GROUP_CONCAT(r.role_id) AS roles
            FROM ?n u
            LEFT JOIN user_profile p ON p.user_id = u.id AND p.deleted_at IS NULL
            LEFT JOIN user_role r ON r.user_id = u.id
            WHERE u.?n = ?s AND u.deleted_at IS NULL
            GROUP BY u.id",
            $this->table,
            $this->primaryKey,
            (string) $id
        );
    } catch (\Exception $e) {
        throw PersistenceException::failure('fetch user by ID', $e);
    }

    if ($row === null) {
        throw EntityNotFoundException::forId((string) $id);
    }

    // Safe handling of roles - explode only if string; handle array otherwise; filter empty values
    if (isset($row['roles']) && $row['roles'] !== '') {
        if (is_string($row['roles'])) {
            $rolesArray = explode(',', $row['roles']);
            $row['roles'] = array_filter($rolesArray, fn($val) => $val !== '');
        } elseif (is_array($row['roles'])) {
            // Just filter empty roles, if any
            $row['roles'] = array_filter($row['roles'], fn($val) => $val !== '');
        } else {
            $row['roles'] = [];
        }
    } else {
        $row['roles'] = [];
    }

    $entity = $this->mapper->toEntity($row);

    if (!$entity instanceof User) {
        throw new \UnexpectedValueException('Expected toEntity() to return a User entity.');
    }

    return $entity;
}

    /**
     * Fetch all active (non-deleted) users with profile and roles.
     * @return User[]
     * @throws PersistenceException
     */
    public function getAll(): array
{
    try {
        $rows = $this->db->getAll(
            "SELECT 
                u.*, 
                p.first_name, p.last_name, p.phone, p.address_line1, p.address_line2, p.city, p.state, p.postal_code, p.country, p.profile_picture, p.preferences, p.gender,
                GROUP_CONCAT(r.role_id) AS roles
            FROM ?n u
            LEFT JOIN user_profile p ON p.user_id = u.id AND p.deleted_at IS NULL
            LEFT JOIN user_role r ON r.user_id = u.id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id",
            $this->table
        );
    } catch (\Exception $e) {
        throw PersistenceException::failure('fetch all users', $e);
    }

    foreach ($rows as &$row) {
        if (isset($row['roles']) && $row['roles'] !== '') {
            if (is_string($row['roles'])) {
                $rolesArray = explode(',', $row['roles']);
                $row['roles'] = array_filter($rolesArray, fn($val) => $val !== '');
            } elseif (is_array($row['roles'])) {
                $row['roles'] = array_filter($row['roles'], fn($val) => $val !== '');
            } else {
                $row['roles'] = [];
            }
        } else {
            $row['roles'] = [];
        }
    }

    $entities = array_map(fn(array $row): EntityInterface => $this->mapper->toEntity($row), $rows);

    foreach ($entities as $entity) {
        if (!$entity instanceof User) {
            throw new \UnexpectedValueException('Expected toEntity() to return User entities.');
        }
    }

    return $entities;
}


    /**
     * Soft delete user (override uses base Repository soft delete implementation).
     * 
     * @param IdentityInterface $id
     * @param IdentityInterface|null $actorId
     * @param string|null $ipBinary
     * @return bool
     * @throws PersistenceException
     */
    public function delete(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $ipBinary = null): bool
    {
        return parent::delete($id, $actorId, $ipBinary);
    }

    /**
     * Fetch user profile by user ID, excluding soft deleted users and profiles.
     *
     * @param IdentityInterface $userId
     * @return array<string,mixed>
     * @throws EntityNotFoundException|PersistenceException
     */
    public function getProfileByUserId(IdentityInterface $userId): array
    {
        try {
            $profile = $this->db->getRow(
                "SELECT first_name, last_name, phone, address_line1, address_line2, city, state, postal_code, country, profile_picture, preferences, gender
                 FROM user_profile p
                 JOIN user u ON u.id = p.user_id
                 WHERE p.user_id = ?s AND p.deleted_at IS NULL AND u.deleted_at IS NULL
                 LIMIT 1",
                (string) $userId
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch user profile by user ID', $e);
        }

        if ($profile === null) {
            throw EntityNotFoundException::forId((string) $userId);
        }

        return $profile;
    }

    /**
     * Save user entity with profile and roles.
     * Uses transactions for atomicity and consistency.
     *
     * @param EntityInterface|User $entity
     * @return bool
     * @throws \LogicException
     * @throws PersistenceException
     */
    public function save(EntityInterface $entity): bool
    {
        if (!$entity instanceof User) {
            throw new \LogicException('Entity must be instance of User');
        }

        $this->beginTransaction();

        try {
            $data = $this->mapper->toDbArray($entity);

            // Separate user table columns from profile columns
            $userTableColumns = [
                'id',
                'username',
                'email',
                'password_hash',
                'status',
                'created_at',
                'created_by',
                'created_ip',
                'updated_at',
                'updated_by',
                'updated_ip',
                'deleted_at',
                'deleted_by',
                'deleted_ip',
            ];

            $userData = array_filter(
                $data,
                fn($key) => in_array($key, $userTableColumns, true),
                ARRAY_FILTER_USE_KEY
            );

            $profileData = array_filter(
                $data,
                fn($key) => !in_array($key, $userTableColumns, true),
                ARRAY_FILTER_USE_KEY
            );

            if (!isset($userData[$this->primaryKey]) || empty($userData[$this->primaryKey])) {
                throw new \LogicException('Entity ID must be set before saving.');
            }

            // Insert or update user
            $this->db->query(
                "INSERT INTO ?n SET ?u ON DUPLICATE KEY UPDATE ?u",
                $this->table,
                $userData,
                $userData
            );

            // Insert or update profile
            $profileExists = $this->db->getOne(
                "SELECT 1 FROM user_profile WHERE user_id = ?s LIMIT 1",
                $entity->getId()
            );

            if ($profileExists) {
                $this->db->query(
                    "UPDATE user_profile SET ?u WHERE user_id = ?s",
                    $profileData,
                    $entity->getId()
                );
            } else {
                $profileData['user_id'] = (string) $entity->getId();
                $this->db->query(
                    "INSERT INTO user_profile SET ?u",
                    $profileData
                );
            }

            // Refresh roles: delete all then insert current roles
            $this->db->query("DELETE FROM user_role WHERE user_id = ?s", $entity->getId());

            $roles = $entity->getRoles();
            if (!empty($roles)) {
                $values = [];
                foreach ($roles as $role) {
                    $values[] = [
                        'user_id' => (string) $entity->getId(),
                        'role_id' => (string) $role->value,
                    ];
                }

                $this->db->query(
                    "INSERT INTO user_role (?n) VALUES ?l",
                    ['user_id', 'role_id'],
                    $values
                );
            }

            $this->commit();

            return true;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw PersistenceException::failure('save user entity', $e);
        }
    }
    public function getRolesByUserId(IdentityInterface $userId): array
    {
        try {
            $rows = $this->db->getAll(
                "SELECT role_id FROM user_role WHERE user_id = ?s",
                (string) $userId
            );
            return array_map(fn($row) => $row['role_id'], $rows);
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch roles by user ID', $e);
        }
    }

}
