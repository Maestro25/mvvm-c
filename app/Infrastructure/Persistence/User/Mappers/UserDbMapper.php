<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User\Mappers;

use App\Infrastructure\Persistence\Shared\Mappers\DbMapper;
use App\Domain\User\Entities\User;
use App\Domain\User\Entities\Profile;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\ProfileId;
use App\Domain\User\ValueObjects\Username;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\PasswordHash;
use App\Domain\User\Enums\UserStatus;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Auth\RBAC\Enums\UserRole;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\User\ValueObjects\Name;
use App\Domain\User\ValueObjects\Phone;
use App\Domain\User\ValueObjects\Address;
use App\Domain\User\Enums\Gender;
use App\Domain\User\ValueObjects\ProfilePicture;
use App\Domain\User\ValueObjects\Preferences;

final class UserDbMapper extends DbMapper implements UserDbMapperInterface
{
    /**
     * @param array<string,mixed> $data DB row including user, profile, and roles
     */
    public function toEntity(array $data): User
    {
        $userId = new UserId($data['id']);
        $profileId = new ProfileId($data['id']); // user_profile uses user_id as PK

        $profile = new Profile(
            profileId: $profileId,
            name: new Name(trim((string)$data['first_name']), trim((string)$data['last_name'])),
            phone: new Phone((string)$data['phone']),
            address: new Address(
                $data['address_line1'] ?? null,
                $data['address_line2'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['postal_code'] ?? null,
                $data['country'] ?? null
            ),
            email: new Email((string)$data['email']),
            profilePicture: isset($data['profile_picture']) && $data['profile_picture'] !== null
                ? new ProfilePicture((string)$data['profile_picture'])
                : null,
            preferences: new Preferences(json_decode($data['preferences'] ?? '{}', true)),
            gender: Gender::from($data['gender'] ?? Gender::UNKNOWN->value)
        );

        $createdInfo = new AuditInfo(
            createdAt: new \DateTimeImmutable($data['created_at']),
            createdBy: isset($data['created_by']) ? new UserId($data['created_by']) : null,
            createdIp: $data['created_ip'] ?? null
        );

        $updatedInfo = new AuditInfo(
            updatedAt: new \DateTimeImmutable($data['updated_at']),
            updatedBy: isset($data['updated_by']) ? new UserId($data['updated_by']) : null,
            updatedIp: $data['updated_ip'] ?? null
        );

        $roles = [];
        if (!empty($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $roleId) {
                $roles[] = UserRole::from($roleId);
            }
        }

        return new User(
            userId: $userId,
            username: new Username((string)$data['username']),
            email: new Email((string)$data['email']),
            passwordHash: new PasswordHash((string)$data['password_hash']),
            status: UserStatus::from($data['status']),
            profile: $profile,
            createdInfo: $createdInfo,
            updatedInfo: $updatedInfo,
            deletedInfo: null,
            roles: $roles
        );
    }

    /**
     * @param User $entity
     * @return array<string,mixed>
     */
    public function toDbArray(EntityInterface $entity): array
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be instance of User');
        }

        $profile = $entity->getProfile();
        $address = $profile->getAddress();

        return [
            // User table columns
            'id' => (string) $entity->getId(),
            'username' => (string) $entity->getUsername(),
            'email' => (string) $entity->getEmail(),
            'password_hash' => (string) $entity->getPasswordHash(),
            'status' => (string) $entity->getStatus(),

            'created_at' => $entity->getCreatedInfo()->createdAt->format('Y-m-d H:i:s.u'),
            'created_by' => $entity->getCreatedInfo()->createdBy !== null ? (string) $entity->getCreatedInfo()->createdBy : null,
            'created_ip' => $entity->getCreatedInfo()->createdIp,

            'updated_at' => $entity->getUpdatedInfo()->updatedAt->format('Y-m-d H:i:s.u'),
            'updated_by' => $entity->getUpdatedInfo()->updatedBy !== null ? (string) $entity->getUpdatedInfo()->updatedBy : null,
            'updated_ip' => $entity->getUpdatedInfo()->updatedIp,

            'deleted_at' => null,
            'deleted_by' => null,
            'deleted_ip' => null,

            // Profile table columns
            'first_name' => trim(explode(' ', (string)$profile->getName())[0] ?? ''),
            'last_name' => trim(explode(' ', (string)$profile->getName())[1] ?? ''),
            'phone' => (string) $profile->getPhone(),
            'address_line1' => $address->getAddressLine1(),
            'address_line2' => $address->getAddressLine2(),
            'city' => $address->getCity(),
            'state' => $address->getState(),
            'postal_code' => $address->getPostalCode(),
            'country' => $address->getCountry(),
            'profile_picture' => $profile->getProfilePicture() ? (string) $profile->getProfilePicture() : null,
            'preferences' => json_encode($profile->getPreferences()),
            'gender' => (string) $profile->getGender()->value,
        ];
    }
}
