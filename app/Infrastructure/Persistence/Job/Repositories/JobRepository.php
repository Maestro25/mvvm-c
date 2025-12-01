<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Job\Repositories;

use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Job\Entities\Job;
use App\Domain\Job\Entities\JobInterface;
use App\Domain\Job\Repositories\JobRepositoryInterface;
use App\Infrastructure\Persistence\Shared\Exceptions\PersistenceException;
use App\Infrastructure\Persistence\Shared\Repositories\Repository;
use App\Infrastructure\Persistence\Job\Mappers\JobDbMapperInterface;
use App\Domain\Shared\Exceptions\EntityNotFoundException;
use DateTimeImmutable;

final class JobRepository extends Repository implements JobRepositoryInterface
{
    protected string $primaryKey = 'id';

    public function __construct(
        \SafeMysql $db,
        JobDbMapperInterface $mapper,
    ) {
        parent::__construct($db, 'background_job', $mapper);
    }

    public function getById(IdentityInterface $id): JobInterface
    {
        try {
            $row = $this->db->getRow(
                "SELECT * FROM ?n WHERE ?n = ?s LIMIT 1",
                $this->table,
                $this->primaryKey,
                (string) $id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch job by id', $e);
        }

        if ($row === null) {
            throw EntityNotFoundException::forId((string) $id);
        }

        return $this->mapper->toEntity($row);
    }

    public function save(EntityInterface $entity): bool
    {
        if (!$entity instanceof Job) {
            throw new \LogicException('Entity must be instance of Job');
        }

        $this->beginTransaction();

        try {
            // Validate job status exists in job_status table before saving
            $statusExists = $this->db->getOne(
                "SELECT 1 FROM job_status WHERE id = ?s",
                $entity->getStatus()->value
            );
            if (!$statusExists) {
                throw new \InvalidArgumentException('Invalid job status: ' . $entity->getStatus()->value);
            }

            $data = $this->mapper->toDbArray($entity);

            if (empty($data[$this->primaryKey])) {
                throw new \LogicException('Entity ID must be set before saving.');
            }

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
            throw PersistenceException::failure('save job entity', $e);
        }
    }

    public function getByStatus(string $status, int $limit = 50, int $offset = 0): array
    {
        try {
            $rows = $this->db->getAll(
                "SELECT b.* FROM ?n b JOIN job_status s ON b.status = s.id WHERE b.status = ?s ORDER BY b.scheduled_at ASC LIMIT ?i OFFSET ?i",
                $this->table,
                $status,
                $limit,
                $offset
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure("fetch jobs by status '$status'", $e);
        }

        return array_map(fn(array $row) => $this->mapper->toEntity($row), $rows);
    }

    public function getScheduledJobsBefore(DateTimeImmutable $before, string $status = 'waiting'): array
    {
        try {
            $rows = $this->db->getAll(
                "SELECT b.* FROM ?n b JOIN job_status s ON b.status = s.id WHERE b.scheduled_at <= ?s AND b.status = ?s ORDER BY b.scheduled_at ASC",
                $this->table,
                $before->format('Y-m-d H:i:s.u'),
                $status
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('fetch scheduled jobs before timestamp', $e);
        }

        return array_map(fn(array $row) => $this->mapper->toEntity($row), $rows);
    }

    public function updateJobStatus(
        IdentityInterface $id,
        string $status,
        ?int $retries = null,
        ?DateTimeImmutable $startedAt = null,
        ?DateTimeImmutable $completedAt = null
    ): bool {
        $statusExists = $this->db->getOne(
            "SELECT 1 FROM job_status WHERE id = ?s",
            $status
        );
        if (!$statusExists) {
            throw new \InvalidArgumentException('Invalid job status: ' . $status);
        }

        $data = ['status' => $status];
        if ($retries !== null) $data['retries'] = $retries;
        if ($startedAt !== null) $data['started_at'] = $startedAt->format('Y-m-d H:i:s.u');
        if ($completedAt !== null) $data['completed_at'] = $completedAt->format('Y-m-d H:i:s.u');

        try {
            $result = $this->db->query(
                "UPDATE ?n SET ?u WHERE ?n = ?s",
                $this->table,
                $data,
                $this->primaryKey,
                (string)$id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('update job status', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('update job status - no rows updated');
        }

        return true;
    }

    public function updateJobScheduledTime(IdentityInterface $id, DateTimeImmutable $scheduledAt): bool
    {
        try {
            $result = $this->db->query(
                "UPDATE ?n SET scheduled_at = ?s WHERE ?n = ?s",
                $this->table,
                $scheduledAt->format('Y-m-d H:i:s.u'),
                $this->primaryKey,
                (string)$id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('update job scheduled time', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('update job scheduled time - no rows updated');
        }

        return true;
    }

    public function updateJobExecutionTime(IdentityInterface $id, DateTimeImmutable $completedAt): bool
    {
        try {
            $result = $this->db->query(
                "UPDATE ?n SET completed_at = ?s WHERE ?n = ?s",
                $this->table,
                $completedAt->format('Y-m-d H:i:s.u'),
                $this->primaryKey,
                (string)$id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('update job execution time', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('update job execution time - no rows updated');
        }

        return true;
    }

    public function updateJobRetryCount(IdentityInterface $id, int $retryCount): bool
    {
        try {
            $result = $this->db->query(
                "UPDATE ?n SET retries = ?i WHERE ?n = ?s",
                $this->table,
                $retryCount,
                $this->primaryKey,
                (string)$id
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('update job retry count', $e);
        }

        if ($result === false) {
            throw PersistenceException::failure('update job retry count - no rows updated');
        }

        return true;
    }

    public function deleteOldJobs(DateTimeImmutable $olderThan): int
    {
        try {
            return (int)$this->db->query(
                "DELETE FROM ?n WHERE (status IN ('completed', 'failed')) AND completed_at <= ?s",
                $this->table,
                $olderThan->format('Y-m-d H:i:s.u')
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('delete old jobs', $e);
        }
    }

    public function search(array $criteria, int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $bindings = [];

        if (isset($criteria['status'])) {
            $where[] = 'b.status = ?s';
            $bindings[] = $criteria['status'];
        }
        if (isset($criteria['job_name'])) {
            $where[] = 'b.job_name = ?s';
            $bindings[] = $criteria['job_name'];
        }
        if (isset($criteria['scheduled_before'])) {
            $where[] = 'b.scheduled_at <= ?s';
            $bindings[] = $criteria['scheduled_before']->format('Y-m-d H:i:s.u');
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $params = array_merge([$this->table], $bindings, [$limit, $offset]);

            $rows = $this->db->getAll(
                "SELECT b.* FROM ?n b JOIN job_status s ON b.status = s.id $whereClause ORDER BY b.scheduled_at ASC LIMIT ?i OFFSET ?i",
                ...$params
            );
        } catch (\Exception $e) {
            throw PersistenceException::failure('search jobs', $e);
        }

        return array_map(fn(array $row) => $this->mapper->toEntity($row), $rows);
    }
}
