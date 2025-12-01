<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use App\Infrastructure\Core\DI\ControllerInvoker;
use App\Infrastructure\Core\DI\MethodInvoker;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class MiddlewareDispatcher implements RequestHandlerInterface
{
    public function __construct(
        private RouteCollectionInterface $routeCollection,
        private MiddlewarePipeline $pipeline,
        private LoggerInterface $logger,
        private ContainerInterface $container,
        private ControllerInvoker $controllerInvoker,
        private MethodInvoker $methodInvoker
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info(
            'MiddlewareDispatcher handling request: ',
            [
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'client_ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
            ]
        );

        $route = $this->routeCollection->match($request);
        if ($route === null) {
            $this->logger->error(
                'No matching route found: ',
                [
                    'uri' => (string) $request->getUri(),
                    'method' => $request->getMethod(),
                    'client_ip' => $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown',
                ]
            );
            throw new RuntimeException('No matching route found.');
        }

        $this->logger->info(
            'Route found for dispatch: ',
            [
                'route_name' => $route->getName(),
                'route_path' => $route->getPath(),
                'http_method' => $route->getMethod()->value,
            ]
        );

        foreach ($route->getMiddlewares() as $middleware) {
            $this->logger->info(
                'Adding route middleware to pipeline: ',
                [
                    'middleware_class' => get_class($middleware),
                    'route_name' => $route->getName(),
                ]
            );
            $this->pipeline->addMiddleware($middleware);
        }

        $finalMiddleware = new class ($route, $this->methodInvoker, $this->controllerInvoker, $request, $this->logger) implements MiddlewareInterface {
            private RouteInterface $route;
            private MethodInvoker $methodInvoker;
            private ControllerInvoker $controllerInvoker;
            private ServerRequestInterface $request;
            private LoggerInterface $logger;

            public function __construct(
                RouteInterface $route,
                MethodInvoker $methodInvoker,
                ControllerInvoker $controllerInvoker,
                ServerRequestInterface $request,
                LoggerInterface $logger
            ) {
                $this->route = $route;
                $this->methodInvoker = $methodInvoker;
                $this->controllerInvoker = $controllerInvoker;
                $this->request = $request;
                $this->logger = $logger;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
            {
                $handler = $this->route->getHandler();
                $params = ['request' => $request];

                $this->logger->info(
                    'Invoking route handler with DI: ',
                    [
                        'route' => $this->route->getName(),
                        'handler_class' => is_object($handler) ? get_class($handler)
                            : (is_array($handler) ? $handler[0] : 'callable'),
                    ]
                );

                if ($handler instanceof RouteHandlerInterface) {
                    return $this->methodInvoker->invokeMethod($handler, 'handle', $params);
                }

                if (is_array($handler) && count($handler) === 2) {
                    return $this->methodInvoker->invokeMethod($handler[0], $handler[1], $params);
                }

                return $this->controllerInvoker->invoke($handler, $params);
            }
        };

        $this->pipeline->addMiddleware($finalMiddleware);
        $response = $this->pipeline->handle($request);

        $this->logger->info(
            'MiddlewarePipeline returned response: ',
            [
                'status_code' => $response->getStatusCode(),
                'route_name' => $route->getName(),
                'uri' => (string) $request->getUri(),
            ]
        );

        return $response;
    }



}
