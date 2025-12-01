<?php
declare(strict_types=1);

namespace App\Domain\User\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

/**
 * Enum representing user genders.
 */
enum Gender: string
{
    use EnumHelpers;

    case MALE = 'male';
    case FEMALE = 'female';
    case UNKNOWN = 'unknown';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::UNKNOWN => '[translate:Unknown]',
            self::PREFER_NOT_TO_SAY => '[translate:Prefer not to say]',
        };
    }
}
