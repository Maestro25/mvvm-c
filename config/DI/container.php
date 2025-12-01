<?php
declare(strict_types=1);

use App\Infrastructure\Core\DI\DIContainer;

return function (DIContainer $container): void {
    require_once __DIR__ . '/infrastructure.php';
    require_once __DIR__ . '/middlewares_registry.php';
    require_once __DIR__ . '/routing_registry.php';
    require_once __DIR__ . '/user_registry.php';
    require_once __DIR__ . '/session_registry.php';
    require_once __DIR__ . '/test_registry.php';

    registerInfrastructure($container);
    registerMiddlewares($container);
    registerRouting($container);
    registerUser($container);
    registerSession($container);
    registerTestSetup($container);
};
