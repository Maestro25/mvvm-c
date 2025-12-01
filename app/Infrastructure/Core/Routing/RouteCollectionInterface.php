<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouteCollectionInterface
{
    public function addRoute(RouteInterface $route): void;

    /**
     * Add a group of routes with optional common middleware.
     *
     * @param RouteInterface[] $routes
     * @param MiddlewareInterface[] $middlewares
     */
    public function addGroup(array $routes, array $middlewares = []): void;

    public function getRouteByName(string $name): ?RouteInterface;

    public function match(ServerRequestInterface $request): ?RouteInterface;

    /**
     * @return RouteInterface[]
     */
    public function getAllRoutes(): array;
}
