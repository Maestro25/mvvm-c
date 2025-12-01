<?php
declare(strict_types=1);

namespace App\Domain\Auth\Factories;

use App\Application\Auth\DTOs\AuthInfoCreationData;
use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\Shared\Entities\EntityInterface;
use App\Domain\Shared\Factories\EntityFactory;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\User\Entities\AuthInfo;
use InvalidArgumentException;

final class AuthInfoFactory extends EntityFactory implements AuthInfoFactoryInterface
{   /**
     * Concrete factories implement this method to instantiate entities based on DTO type
     * @param IdentityInterface $id
     * @param EntityCreationDataInterface $dto
     * @return EntityInterface
     */
    protected function instantiateEntity(IdentityInterface $id, EntityCreationDataInterface $dto): EntityInterface
{
    if (!$dto instanceof AuthInfoCreationData) {
        throw new InvalidArgumentException('Expected AuthInfoCreationData DTO.');
    }

    return new AuthInfo(
        $id,
        $dto->failedLoginAttempts,
        $dto->lastFailedLoginAt,
        $dto->lockedUntil,
        $dto->passwordResetToken,
        $dto->emailVerificationToken,
        $dto->rememberMeToken,
        $dto->lastLoginAt
    );
}

}
