<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RouteMatcherInterface
 *
 * Contract for matching a ServerRequest to a Route.
 */
interface RouteMatcherInterface
{
    /**
     * Match a ServerRequest to a Route.
     *
     * @param ServerRequestInterface $request
     * @return Route|null
     */
    public function match(ServerRequestInterface $request): ?RouteInterface;
}

