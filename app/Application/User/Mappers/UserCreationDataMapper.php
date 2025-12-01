<?php
declare(strict_types=1);

namespace App\Application\User\Mappers;

use App\Application\Shared\Mappers\DtoMapper;
use App\Application\User\DTOs\UserCreationData;
use App\Domain\User\Entities\User;
use App\Domain\Shared\Entities\EntityInterface;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\User\Entities\Profile;

final class UserCreationDataMapper extends DtoMapper implements UserCreationDataMapperInterface
{
    public function __construct()
    {
        // No dependencies needed
    }

    public function toDTO(EntityInterface $entity): UserCreationData
    {
        if (!$entity instanceof User) {
            throw new \InvalidArgumentException('Entity must be instance of User');
        }

        $profile = $entity->getProfile();
        $roles = $entity->getRoles();

        return new UserCreationData(
            userId: $entity->getId(),
            username: $entity->getUsername(),
            email: $entity->getEmail(),
            passwordHash: $entity->getPasswordHash(),
            status: $entity->getStatus(),
            profileId: $profile->getId(),
            name: $profile->getName(),
            phone: $profile->getPhone(),
            address: $profile->getAddress(),
            gender: $profile->getGender(),
            profilePicture: $profile->getProfilePicture(),
            preferences: $profile->getPreferences(),
            roles: $roles,
        );
    }

    public function toEntity(IdentityInterface $id, EntityCreationDataInterface $dto): User
    {
        if (!$dto instanceof UserCreationData) {
            throw new \InvalidArgumentException('DTO must be instance of UserCreationData');
        }
        $profile = new Profile(
            $dto->profileId,
            $dto->name,
            $dto->phone,
            $dto->address,
            $dto->email,
            $dto->profilePicture,
            $dto->preferences,
            $dto->gender
        );
        // Direct instantiation of User entity
        return new User(
            $id,
            $dto->username,
            $dto->email,
            $dto->passwordHash,
            $dto->status,
            $profile,
            null,
            null,
            null,
            $dto->roles,
        );
    }
}
