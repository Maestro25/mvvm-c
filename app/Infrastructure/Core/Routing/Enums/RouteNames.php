<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

final class RouteNames
{
    use EnumHelpers;
    
    public const STATIC_ASSETS = 'static_assets';
    public const LOGIN_SUBMIT = 'login_submit';
    public const REGISTER_SUBMIT = 'register_submit';
    public const LOGOUT = 'logout';
    public const AUTH_CHECK = 'auth_check';
    public const TEST_PAGE = 'test_page';
    public const FRONTEND = 'frontend';

    public static function getAllRoutes(): array
    {
        return [
            self::STATIC_ASSETS,
            self::LOGIN_SUBMIT,
            self::REGISTER_SUBMIT,
            self::LOGOUT,
            self::AUTH_CHECK,
            self::TEST_PAGE,
            self::FRONTEND,
        ];
    }
}
