<?php
declare(strict_types=1);

namespace App\Domain\Auth\RBAC\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing user roles.
 */
enum UserRole: string
{
    use EnumHelpers;

    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case USER = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::EDITOR => 'Editor',
            self::USER => 'User',
        };
    }
}
