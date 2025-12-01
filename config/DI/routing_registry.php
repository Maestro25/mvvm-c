<?php
declare(strict_types=1);

use App\Application\Session\Services\SessionService;
use App\Common\Enums\ServiceLifetime;
use App\Infrastructure\Core\DI\DIContainer;
use App\Infrastructure\Core\DI\ControllerInvoker;
use App\Infrastructure\Core\DI\MethodInvoker;
use App\Infrastructure\Core\Routing\{
    MiddlewareDispatcher,
    MiddlewarePipeline,
    RouteCollection,
    RouteMatcher,
    Router,
    UrlGenerator,
    RouteCollectionInterface,
    RouteLoader,
    RouteMatcherInterface,
    RouterInterface
};
use App\Presentation\Shared\Coordinators\TestPageCoordinator;
use Laminas\Diactoros\ResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

function registerRouting(DIContainer $container): void
{

    // Bind RouteCollectionInterface with closure because it needs Logger injected
    // In your DI container config or bootstrap

    // $container->singleton(RouteCollectionInterface::class, function ($c) {
    //     try {
    //         $logger = $c->get(LoggerInterface::class);
    //         $sessionService = $c->get(SessionService::class);
    //         return RouteLoader::loadRoutes($logger, $sessionService);
    //     } catch (\Throwable $e) {
    //         error_log('RouteCollectionInterface factory failed: ' . $e->getMessage());
    //         throw $e;
    //     }
    // });
    $container->bind(RouteCollectionInterface::class, function (DIContainer $c) {
        $logger = $c->get(LoggerInterface::class);
        return RouteLoader::loadRoutes($logger, $c);
    }, ServiceLifetime::SINGLETON);


    // Direct class binding for simple routable components
    $container->bind(RouteMatcherInterface::class, function (DIContainer $c) {
        return new RouteMatcher(
            $c->get(RouteCollectionInterface::class),
            $c->get(LoggerInterface::class),
        );
    }, ServiceLifetime::SINGLETON);
    $container->bind(RouterInterface::class, Router::class, ServiceLifetime::SINGLETON);
    $container->bind(MiddlewarePipeline::class, MiddlewarePipeline::class, ServiceLifetime::SINGLETON);
    $container->bind(ControllerInvoker::class, ControllerInvoker::class, ServiceLifetime::SINGLETON);
    $container->bind(MethodInvoker::class, MethodInvoker::class, ServiceLifetime::SINGLETON);

    // MiddlewareDispatcher needs multiple dependencies, so bind with closure
    $container->bind(MiddlewareDispatcher::class, function (DIContainer $c) {
        return new MiddlewareDispatcher(
            $c->get(RouteCollectionInterface::class),
            $c->get(MiddlewarePipeline::class),
            $c->get(LoggerInterface::class),
            $c->get(ContainerInterface::class),
            $c->get(ControllerInvoker::class),
            $c->get(MethodInvoker::class)
        );
    }, ServiceLifetime::SINGLETON);

    $container->bind(UrlGenerator::class, UrlGenerator::class, ServiceLifetime::SINGLETON);
}
;


