<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use RuntimeException;

/**
 * Class UrlGenerator
 *
 * Generates URLs from route names and parameters.
 * Fully validates route presence and parameter completeness.
 * Encodes URL parameters for safety.
 */
final class UrlGenerator implements UrlGeneratorInterface
{
    public function __construct(private RouteCollectionInterface $routes) {}

    public function generate(string $routeName, array $params = []): string
    {
        $route = $this->routes->getRouteByName($routeName);
        if ($route === null) {
            throw new RuntimeException(sprintf('Route "%s" not found.', $routeName));
        }

        $path = $route->getPath();

        preg_match_all('/\{(\w+)\}/', $path, $matches);
        $requiredParams = $matches[1] ?? [];

        foreach ($requiredParams as $paramName) {
            if (!array_key_exists($paramName, $params)) {
                throw new RuntimeException(sprintf('Missing required parameter "%s" for route "%s".', $paramName, $routeName));
            }
        }

        foreach ($params as $key => $value) {
            $encodedValue = urlencode((string)$value);
            $path = str_replace(sprintf('{%s}', $key), $encodedValue, $path);
        }

        if ($path === '') {
            $path = '/';
        }

        return $path;
    }
}

