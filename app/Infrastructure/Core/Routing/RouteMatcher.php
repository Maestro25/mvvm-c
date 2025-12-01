<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class RouteMatcher implements RouteMatcherInterface
{
    public function __construct(private RouteCollectionInterface $routes, private LoggerInterface $logger)
    {
    }

    public function match(ServerRequestInterface $request): ?RouteInterface
    {
        $method = strtoupper($request->getMethod());
        $path = rtrim($request->getUri()->getPath(), '/') ?: '/';

        foreach ($this->routes->getAllRoutes() as $route) {
            if ($route->getMethod()->value !== $method) {
                continue;
            }

            $params = [];
            if ($this->matchPath($route->getPath(), $path, $params)) {
                $this->logger->info(
                    'Route matched: ',
                    [
                        'route_name' => $route->getName(),
                        'route_path' => $route->getPath(),
                        'request_path' => $path,
                        'http_method' => $method,
                        'params' => $params,
                    ]
                );
                return $route;
            }
        }

        $this->logger->warning(
            'No matching route found: ',
            [
                'request_path' => $path,
                'http_method' => $method,
            ]
        );
        return null;
    }


    private function matchPath(string $routePath, string $requestPath, array &$params = []): bool
    {
        $pattern = preg_replace_callback(
            '/\{(\w+)\}/',
            fn($matches) => '(?P<' . $matches[1] . '>[^\/]+)',
            preg_quote($routePath, '#')
        );

        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            $params = array_filter(
                $matches,
                fn($key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );
            return true;
        }

        return false;
    }
}
