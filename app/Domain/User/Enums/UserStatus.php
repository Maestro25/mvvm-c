<?php
declare(strict_types=1);

namespace App\Domain\User\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

enum UserStatus: string
{
    use EnumHelpers;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::BANNED => 'Banned',
            self::DELETED => 'Deleted',
        };
    }
}
