<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Infrastructure\Core\Routing\RouteHandlerInterface;

final class RouteLoader
{
    public static function loadRoutes(
        LoggerInterface $logger,
        ContainerInterface $container
    ): RouteCollectionInterface {
        $routeCollection = new RouteCollection($logger);
        $routeConfigs = require __DIR__ . '/../../../../config/routes.php';

        foreach ($routeConfigs as $config) {
            $handlerInstance = null;
            if (is_string($config['handler']) && $container->has($config['handler'])) {
                $handlerInstance = $container->get($config['handler']);
            }

            $actionMethod = $config['action'] ?? 'handle';

            // ✅ FIXED: Proper anonymous class structure
            $handler = $handlerInstance instanceof RouteHandlerInterface
                ? $handlerInstance
                : new class($handlerInstance, $actionMethod) implements RouteHandlerInterface {
                    private $instance;
                    private $actionMethod;

                    public function __construct($instance, string $actionMethod) {
                        $this->instance = $instance;
                        $this->actionMethod = $actionMethod;
                    }

                    public function handle(ServerRequestInterface $request): ResponseInterface {
                        if (!$this->instance || !method_exists($this->instance, $this->actionMethod)) {
                            throw new \RuntimeException(
                                "Handler " . ($this->instance ? $this->instance::class : 'null') . 
                                " missing method {$this->actionMethod}"
                            );
                        }
                        return $this->instance->{$this->actionMethod}($request);
                    }
                }; // ✅ <- Proper semicolon here

            $route = new Route(
                $config['name'],
                $config['method'], 
                $config['path'],
                $handler,
                $config['middlewares'] ?? []
            );

            $routeCollection->addRoute($route);
        }

        return $routeCollection;
    }
}
