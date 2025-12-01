<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Enums;

use App\Domain\Shared\Traits\EnumHelpers;

final class RoutePaths
{
    use EnumHelpers;
    
    public const STATIC_ASSETS = '/{asset:.*\.(ico|png|jpg|jpeg|gif|svg|css|js|woff|woff2|ttf|eot|mp4|webm|ogg|mp3|pdf|zip|gz)}';
    public const LOGIN_SUBMIT = '/login';
    public const REGISTER_SUBMIT = '/register';
    public const LOGOUT = '/logout';
    public const AUTH_CHECK = '/authcheck';
    public const TEST_PAGE = '/test';
    public const FRONTEND = '/{path:.*}';

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
