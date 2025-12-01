<?php
declare(strict_types=1);

namespace App\Domain\Auth\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing user account statuses.
 */
enum AccountStatus: string
{
    use EnumHelpers;

    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case PENDING_VERIFICATION = 'pending_verification';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::PENDING_VERIFICATION => 'Pending Verification',
            self::DELETED => 'Deleted',
        };
    }
}
