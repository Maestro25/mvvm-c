<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class RouteCollection implements RouteCollectionInterface
{
    /**
     * @var RouteInterface[]
     */
    private array $routes = [];

    /**
     * @var array<string, RouteInterface>
     */
    private array $routesByName = [];

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Add a Route to the collection, enforcing unique names and logging.
     *
     * @param RouteInterface $route
     * @throws RuntimeException if a duplicate route name is detected
     */
    public function addRoute(RouteInterface $route): void
    {
        $name = $route->getName();
        if (isset($this->routesByName[$name])) {
            $this->logger->error('Attempted to add duplicate route', ['routeName' => $name]);
            throw new RuntimeException(sprintf('Route with name "%s" already exists.', $name));
        }
        $this->routes[] = $route;
        $this->routesByName[$name] = $route;
        $this->logger->info('Route registered', [
            'routeName' => $name,
            'method' => $route->getMethod()->value,
            'path' => $route->getPath()
        ]);
    }

    /**
     * Convenience method to add grouped routes with common middleware and logging.
     *
     * @param array<RouteInterface> $routes
     * @param MiddlewareInterface[] $middlewares
     */
    public function addGroup(array $routes, array $middlewares = []): void
    {
        foreach ($routes as $route) {
            // Merge group middlewares with route-specific middlewares
            $mergedMiddlewares = array_merge($middlewares, $route->getMiddlewares());
            $route->setMiddlewares($mergedMiddlewares);
            // Add each route individually; addRoute already logs each addition
            $this->addRoute($route);
        }
    }

    /**
     * Retrieve a Route by its unique name.
     *
     * @param string $name
     * @return RouteInterface|null
     */
    public function getRouteByName(string $name): ?RouteInterface
    {
        return $this->routesByName[$name] ?? null;
    }

    /**
     * Match a ServerRequest to a Route based on method and path.
     *
     * @param ServerRequestInterface $request
     * @return RouteInterface|null
     */
    public function match(ServerRequestInterface $request): ?RouteInterface
    {
        $method = strtoupper($request->getMethod());
        $uriPath = rtrim($request->getUri()->getPath(), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route->getMethod()->value !== $method) {
                continue;
            }
            if ($route->getPath() === $uriPath) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Returns all routes in this collection.
     *
     * @return array<RouteInterface>
     */
    public function getAllRoutes(): array
    {
        return $this->routes;
    }
}
