<?php
declare(strict_types=1);

namespace App\Domain\Auth\RBAC\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing common permission levels.
 */
enum PermissionLevel: string
{
    use EnumHelpers;

    case READ = 'read';       // View access
    case WRITE = 'write';     // Create or modify access
    case DELETE = 'delete';   // Remove or delete access
    case EXECUTE = 'execute'; // Perform an action or operation
    case MANAGE = 'manage';   // Full control over resource or module

    public function label(): string
    {
        return match ($this) {
            self::READ => 'Read',
            self::WRITE => 'Write',
            self::DELETE => 'Delete',
            self::EXECUTE => 'Execute',
            self::MANAGE => 'Manage',
        };
    }
}
