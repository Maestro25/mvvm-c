<?php
declare(strict_types=1);

namespace App\Domain\Session\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

enum SessionStatus: string
{
    use EnumHelpers;
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::REVOKED => 'Revoked',
            self::EXPIRED => 'Expired',
        };
    }
}
