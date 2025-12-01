<?php
declare(strict_types=1);

use App\Infrastructure\Core\Routing\Middlewares\ExceptionHandlingMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\LoggingMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\AuthenticationMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\NotFoundMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\SessionMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\StaticAssetsMiddleware;
use App\Infrastructure\Core\Routing\Middlewares\StaticFileMiddleware; // <-- NEW: Static files first
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use App\Infrastructure\Core\Routing\MiddlewarePipeline;
use App\Application\Session\Services\SessionService;

/**
 * Register global middlewares once in this bootstrap or config file
 *
 * @param MiddlewarePipeline $pipeline
 * @param LoggerInterface $logger
 * @param ResponseFactoryInterface $responseFactory
 * @param SessionService $sessionService
 * @return void
 */
function registerGlobalMiddlewares(
    MiddlewarePipeline $pipeline,
    ContainerInterface $container,
): void {
    

   
    $pipeline->addMiddleware($container->get(StaticAssetsMiddleware::class));
    $pipeline->addMiddleware($container->get(ExceptionHandlingMiddleware::class));
    $pipeline->addMiddleware($container->get(LoggingMiddleware::class));
    $pipeline->addMiddleware($container->get(SessionMiddleware::class));
    

    // Global exception handler to catch all errors
    // $pipeline->addMiddleware(new ExceptionHandlingMiddleware($logger, $responseFactory, displayErrorDetails: false));

    // // Request/response logging
    // $pipeline->addMiddleware(new LoggingMiddleware($logger));

    // // Session middleware should be added before auth to start/resume session
    // $pipeline->addMiddleware(new SessionMiddleware($sessionService, $logger));

    // // Auth middleware to protect all routes globally
    // $pipeline->addMiddleware(new AuthenticationMiddleware($responseFactory));
}
