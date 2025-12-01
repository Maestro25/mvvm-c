<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class Router implements RouterInterface
{
    private RouteCollectionInterface $routeCollection;
    private RouteMatcherInterface $routeMatcher;

    public function __construct(
        RouteCollectionInterface $routeCollection,
        RouteMatcherInterface $routeMatcher
    ) {
        $this->routeCollection = $routeCollection;
        $this->routeMatcher = $routeMatcher;
    }

    public function match(ServerRequestInterface $request): RouteInterface
    {
        $route = $this->routeMatcher->match($request);
        if ($route === null) {
            throw new RuntimeException(
                sprintf('No matching route found for the request URI: %s', (string)$request->getUri())
            );
        }
        return $route;
    }

    public function getRouteCollection(): RouteCollectionInterface
    {
        return $this->routeCollection;
    }

    // Add a route to the underlying RouteCollection
    public function addRoute(RouteInterface $route): void
    {
        $this->routeCollection->addRoute($route);
    }

    // Return all routes from the underlying RouteCollection
    public function getRoutes(): array
{
    // Delegate to existing method in RouteCollection
    return $this->routeCollection->getAllRoutes();
}

}
