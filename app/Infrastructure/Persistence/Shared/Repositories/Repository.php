<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Shared\Repositories;


use SafeMysql;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\Exceptions\EntityNotFoundException;
use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Infrastructure\Persistence\Shared\Exceptions\PersistenceException;
use App\Infrastructure\Persistence\Shared\Mappers\DbMapperInterface;

/**
 * Abstract repository following MVVM-C, Clean Architecture, SOLID principles.
 * Uses SafeMysql for database operations with robust error handling and custom exceptions.
 */
abstract class Repository implements RepositoryInterface
{
    protected string $primaryKey = 'id';

    public function __construct(
        protected readonly SafeMysql $db,
        protected readonly string $table,
        protected readonly DbMapperInterface $mapper,
    ) {
    }

    public function getById(IdentityInterface $id): EntityInterface
    {
        try {
            $row = $this->db->getRow(
                "SELECT * FROM ?n WHERE ?n = ?s LIMIT 1",
                $this->table,
                $this->primaryKey,
                (string) $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch by ID', $e);
        }

        if ($row === null) {
            throw EntityNotFoundException::forId((string) $id);
        }

        return $this->mapper->toEntity($row);
    }

    public function getAll(): array
    {
        try {
            $rows = $this->db->getAll("SELECT * FROM ?n", $this->table);
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch all', $e);
        }

        return array_map(fn(array $row): EntityInterface => $this->mapper->toEntity($row), $rows);
    }

    public function save(EntityInterface $entity): bool
    {   
        $data = $this->mapper->toDbArray($entity);

        if (!isset($data[$this->primaryKey]) || empty($data[$this->primaryKey])) {
            throw new \LogicException('Entity ID must be set before saving when using UUIDs.');
        }

        $id = $data[$this->primaryKey];
        unset($data[$this->primaryKey]);

        try {
            $this->db->query(
                "UPDATE ?n SET ?u WHERE ?n = ?s",
                $this->table,
                $data,
                $this->primaryKey,
                $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('save entity', $e);
        }

        return true;
    }

    public function delete(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $ipBinary = null): bool
    {
        $data = ['deleted_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s.u')];

        if ($actorId !== null) {
            $data['deleted_by'] = (string) $actorId;
        }
        if ($ipBinary !== null) {
            $data['deleted_ip'] = $ipBinary;
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
            throw PersistenceException::failure('soft delete entity', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('soft delete entity - no rows updated');
        }

        return true;
    }

    public function beginTransaction(): void
    {
        $this->db->query('START TRANSACTION');
    }

    public function commit(): void
    {
        $this->db->query('COMMIT');
    }

    public function rollBack(): void
    {
        $this->db->query('ROLLBACK');
    }
}
