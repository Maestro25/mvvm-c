<?php
declare(strict_types=1);

namespace App\Domain\Shared\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

enum NotificationType: string
{
    use EnumHelpers;

    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH_NOTIFICATION = 'push_notification';
    case IN_APP_MESSAGE = 'in_app_message';

    public function label(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::PUSH_NOTIFICATION => 'Push Notification',
            self::IN_APP_MESSAGE => 'In-App Message',
        };
    }
}
