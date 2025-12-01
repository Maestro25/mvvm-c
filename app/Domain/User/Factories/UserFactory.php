<?php
declare(strict_types=1);

namespace App\Domain\User\Factories;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Application\Shared\DTOs\AuditDataInterface;
use App\Application\User\DTOs\UserCreationData;
use App\Domain\Shared\Factories\EntityFactory;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\Factories\AuditInfoFactoryInterface;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\User\Entities\Profile;
use App\Domain\User\Entities\User;

final class UserFactory extends EntityFactory implements UserFactoryInterface
{
    public function __construct(
        protected AuditInfoFactoryInterface $auditInfoFactory
    ) {
        parent::__construct($auditInfoFactory);
    }

    protected function instantiateEntity(
        IdentityInterface $id,
        EntityCreationDataInterface $dto,
        AuditInfo $createdInfo,
        AuditInfo $updatedInfo,
        ?AuditInfo $deletedInfo
    ): User {
        if (!$dto instanceof UserCreationData) {
            throw new \InvalidArgumentException('Expected UserCreationData');
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

        return new User(
            userId: $id,
            username: $dto->username,
            email: $dto->email,
            passwordHash: $dto->passwordHash,
            status: $dto->status,
            profile: $profile,
            createdInfo: $createdInfo,
            updatedInfo: $updatedInfo,
            deletedInfo: $deletedInfo,
            roles: $dto->roles
        );
    }
}
