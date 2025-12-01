<?php
declare(strict_types=1);

use App\Infrastructure\Core\Routing\Middlewares\NotFoundMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\StaticAssetsMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\ExceptionHandlingMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\LoggingMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\SessionMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\AuthenticationMiddleware;
use App\Application\Session\Services\SessionService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use App\Infrastructure\Core\DI\DIContainer;
use App\Config\EnvironmentLoader;
use Monolog\Level;

/*
 * Middleware DI bindings following your established pattern.
 * Loaded by config/services.php after core infrastructure.
 */

function registerMiddlewares(DIContainer $container): void
{
    // 1. StaticAssetsMiddleware (FIRST - serves /assets/* immediately)
    $container->singleton(StaticAssetsMiddleware::class, function (DIContainer $c) {
    return new StaticAssetsMiddleware(
        $c->get(LoggerInterface::class),
        // Concatenate base path with relative path in platform-independent way:
        rtrim($c->getRaw('BASE_PATH'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'favicons'
    );
});


    // 2. ExceptionHandlingMiddleware (catches everything early)
    $container->singleton(ExceptionHandlingMiddleware::class, function (DIContainer $c) {
        return new ExceptionHandlingMiddleware(
            $c->get(LoggerInterface::class),
            $c->get(ResponseFactoryInterface::class),
            (bool) ($_ENV['APP_DEBUG'] ?? false)
        );
    });

    // 3. LoggingMiddleware (logs after static assets, before session)
    $container->singleton(LoggingMiddleware::class, function (DIContainer $c) {
        return new LoggingMiddleware($c->get(LoggerInterface::class));
    });

    // 4. SessionMiddleware (starts session before auth)
    $container->singleton(SessionMiddleware::class, function (DIContainer $c) {
        return new SessionMiddleware(
            $c->get(SessionService::class),
            $c->get(LoggerInterface::class)
        );
    });

    // 5. AuthenticationMiddleware (LAST - protects routes)
    $container->singleton(AuthenticationMiddleware::class, function (DIContainer $c) {
        return new AuthenticationMiddleware($c->get(ResponseFactoryInterface::class));
    });
    $container->singleton(NotFoundMiddleware::class, function ($c) {
        return new NotFoundMiddleware(
            $c->get(LoggerInterface::class),
            new Psr17Factory()
        );
    });

}
