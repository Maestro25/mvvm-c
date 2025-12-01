<?php
declare(strict_types=1);

use App\Infrastructure\Core\Routing\Enums\HttpMethod;
use App\Infrastructure\Core\Routing\Enums\RouteNames;
use App\Infrastructure\Core\Routing\Enums\RoutePaths;
use App\Infrastructure\Core\Routing\Middlewares\StaticAssetsMiddleware;
use App\Presentation\Shared\Coordinators\TestPageCoordinator;
use App\AppCoordinator;  // ✅ Add for catch-all

return [
    // ✅ #1 STATIC ASSETS (MUST BE FIRST - catches /assets/*)
    [
        'name' => RouteNames::STATIC_ASSETS,
        'method' => HttpMethod::GET,
        'path' => '/assets/{path:.*}',
        'handler' => StaticAssetsMiddleware::class,
        'action' => 'process',
        'middlewares' => [],
    ],

    // ✅ #2 TEST PAGE
    [
        'name' => RouteNames::TEST_PAGE,
        'method' => HttpMethod::GET,
        'path' => RoutePaths::TEST_PAGE,
        'handler' => TestPageCoordinator::class,
        'action' => 'start',
        'middlewares' => [],
    ],
    [
        'name' => 'favicon',
        'method' => HttpMethod::GET,
        'path' => '/favicon.ico',
        'handler' => StaticAssetsMiddleware::class,
        'action' => 'process',
        'middlewares' => [],
    ],

    // ✅ #3 CATCH-ALL (LAST - handles /favicon.ico, /robots.txt, etc.)
    [
        'name' => 'catch_all',
        'method' => HttpMethod::GET,
        'path' => '{path:.*}',
        'handler' => TestPageCoordinator::class,
        'action' => 'handleNotFound',
    ],
];
