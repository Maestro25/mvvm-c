<?php
declare(strict_types=1);

namespace App\Domain\Auth\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing two-factor authentication methods.
 */
enum TwoFactorMethod: string
{
    use EnumHelpers;

    case SMS = 'sms';
    case AUTHENTICATOR_APP = 'authenticator_app';
    case EMAIL = 'email';

    public function label(): string
    {
        return match ($this) {
            self::SMS => 'SMS',
            self::AUTHENTICATOR_APP => 'Authenticator App',
            self::EMAIL => 'Email',
        };
    }
}
