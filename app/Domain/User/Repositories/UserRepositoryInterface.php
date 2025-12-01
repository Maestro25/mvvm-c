<?php
declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\Repositories\RepositoryInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\User\Entities\User;
use App\Domain\Shared\Exceptions\EntityNotFoundException;
use App\Infrastructure\Persistence\Shared\Exceptions\PersistenceException;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Fetch a User entity by its unique identifier.
     *
     * @param IdentityInterface $id
     * @return User
     * @throws EntityNotFoundException If the user was not found.
     * @throws PersistenceException On low-level persistence failures.
     */
    public function getById(IdentityInterface $id): User;

    /**
     * Fetch all active (non-deleted) User entities.
     *
     * @return User[]
     * @throws PersistenceException On low-level persistence failures.
     */
    public function getAll(): array;

    /**
     * Soft delete a user by ID.
     *
     * @param IdentityInterface $id
     * @param IdentityInterface|null $actorId Optional ID of the actor performing deletion.
     * @param string|null $ipBinary Optional IP address in binary form.
     * @return bool True on success.
     * @throws PersistenceException On low-level persistence failures.
     */
    public function delete(IdentityInterface $id, ?IdentityInterface $actorId = null, ?string $ipBinary = null): bool;

    /**
     * Fetch user profile information by user ID.
     *
     * @param IdentityInterface $userId
     * @return array<string,mixed> Associative array of profile data.
     * @throws EntityNotFoundException If user/profile not found.
     * @throws PersistenceException On persistence failure.
     */
    public function getProfileByUserId(IdentityInterface $userId): array;

}
