<?php
declare(strict_types=1);

namespace App\Application\User\DTOs;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\User\Enums\UserStatus;
use App\Domain\Auth\RBAC\Enums\UserRole;
use App\Domain\User\ValueObjects\Email;
use App\Domain\User\ValueObjects\PasswordHash;
use App\Domain\User\ValueObjects\Username;
use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\User\ValueObjects\ProfilePicture;
use App\Domain\User\ValueObjects\Preferences;
use App\Domain\User\ValueObjects\Name;
use App\Domain\User\ValueObjects\Phone;
use App\Domain\User\ValueObjects\Address;
use App\Domain\User\Enums\Gender;
use App\Domain\Shared\ValueObjects\ProfileId;
use App\Domain\Shared\ValueObjects\UserId;

final class UserCreationData implements EntityCreationDataInterface
{
    /**
     * @param UserRole[] $roles
     */
    public function __construct(
        public readonly UserId $userId,
        public readonly Username $username,
        public readonly Email $email,
        public readonly PasswordHash $passwordHash,
        public readonly UserStatus $status,
        public readonly ProfileId $profileId,
        public readonly Name $name,
        public readonly Phone $phone,
        public readonly Address $address,
        public readonly Gender $gender,
        public readonly ?ProfilePicture $profilePicture,
        public readonly Preferences $preferences,
        public readonly array $roles = []
    ) {}
}
