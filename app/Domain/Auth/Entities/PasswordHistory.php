<?php
declare(strict_types=1);

namespace App\Domain\Auth\Entities;

use App\Domain\Shared\Entities\Entity;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\PasswordHistoryId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\User\ValueObjects\PasswordHash;
use InvalidArgumentException;

/**
 * Entity representing a historical password record for a user,
 * with audit information encapsulated by AuditInfo value objects.
 */
final class PasswordHistory extends Entity
{   /**
    * Constructor for PasswordHistory entity.
    *
    * @param PasswordHistoryId $id Nullable for new records before persistence
    * @param UserId $userId User identity value object
    * @param PasswordHash $passwordHash Hashed password value object
    * @param AuditInfo $createdInfo Audit info for creation timestamp, user, ip
    * @param AuditInfo $updatedInfo Audit info for last update timestamp, user, ip
    */
    public function __construct(
        PasswordHistoryId $id,
        private UserId $userId,
        private PasswordHash $passwordHash,
        private AuditInfo $createdInfo,
        private AuditInfo $updatedInfo
    ) {
        if (empty((string) $id)) {
            throw new InvalidArgumentException('ID cannot be empty.');
        }
        parent::__construct($id);
    }

    public function getId(): PasswordHistoryId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    /**
     * Updates the password hash if different and updates the updatedInfo audit data.
     *
     * @param PasswordHash $passwordHash
     * @param AuditInfo $updateInfo Audit info for this update action
     */
    public function setPasswordHash(PasswordHash $passwordHash, AuditInfo $updateInfo): void
    {
        if (!$this->passwordHash->equals($passwordHash)) {
            $this->passwordHash = $passwordHash;
            $this->updatedInfo = $updateInfo;
        }
    }

    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    public function getUpdatedInfo(): AuditInfo
    {
        return $this->updatedInfo;
    }
}
