<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;

/**
 * RouterInterface defines methods to match routes and optionally manage collections.
 */
interface RouterInterface
{
    /**
     * Matches the route for a given PSR-7 ServerRequest.
     *
     * @param ServerRequestInterface $request
     * @return RouteInterface|null Returns matched route or null if none matches.
     */
    public function match(ServerRequestInterface $request): ?RouteInterface;

    /**
     * Adds a route to the router.
     *
     * @param RouteInterface $route
     * @return void
     */
    public function addRoute(RouteInterface $route): void;

    /**
     * Returns all registered routes.
     *
     * @return RouteInterface[]
     */
    public function getRoutes(): array;
}
