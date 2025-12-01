<?php
declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\Shared\Entities\Entity;
use App\Domain\User\Enums\UserStatus;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\PasswordHash;
use App\Domain\User\ValueObjects\Username;
use App\Domain\Auth\RBAC\Enums\UserRole;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\Validation\Exceptions\ValidationException;
use App\Domain\User\Enums\Gender;
use App\Domain\User\Validation\Traits\UserValidationGuard;
use App\Domain\User\ValueObjects\Address;
use App\Domain\User\ValueObjects\Name;
use App\Domain\User\ValueObjects\Phone;
use App\Domain\User\ValueObjects\Preferences;
use App\Domain\User\ValueObjects\ProfilePicture;

final class User extends Entity
{
    

    /**
     * @param UserRole[] $roles
     */
    public function __construct(
        private UserId $userId,
        private Username $username,
        private Email $email,
        private PasswordHash $passwordHash,
        private UserStatus $status,
        private Profile $profile,
        private ?AuditInfo $createdInfo,
        private ?AuditInfo $updatedInfo,
        private ?AuditInfo $deletedInfo = null,
        private array $roles = []
    ) {
        parent::__construct($userId);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function setUsername(Username $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(PasswordHash $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function activate(): void
    {
        $this->status = UserStatus::ACTIVE;
    }

    public function deactivate(): void
    {
        $this->status = UserStatus::INACTIVE;
    }

    /**
     * @return UserRole[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function addRole(UserRole $role): void
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = $role;

        }
    }

    public function removeRole(UserRole $role): void
    {
        $this->roles = array_filter($this->roles, fn(UserRole $r) => $r !== $role);
    }

    public function clearRoles(): void
    {
        $this->roles = [];
    }

    public function getCreatedInfo(): AuditInfo
    {
        return $this->createdInfo;
    }

    public function getUpdatedInfo(): AuditInfo
    {
        return $this->updatedInfo;
    }

    public function getDeletedInfo(): ?AuditInfo
    {
        return $this->deletedInfo;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function changeProfileName(Name $name): void
    {
        $this->profile->setName($name);
    }

    public function changeProfilePhone(Phone $phone): void
    {
        $this->profile->setPhone($phone);
    }

    public function changeProfileAddress(Address $address): void
    {
        $this->profile->setAddress($address);
    }

    public function changeProfileEmail(Email $email): void
    {
        $this->profile->setEmail($email);
    }

    public function changeProfilePicture(?ProfilePicture $picture): void
    {
        $this->profile->setProfilePicture($picture);
    }

    public function changeProfilePreferences(Preferences $preferences): void
    {
        $this->profile->setPreferences($preferences);
    }

    public function changeProfileGender(Gender $gender): void
    {
        $this->profile->setGender($gender);
    }
}
