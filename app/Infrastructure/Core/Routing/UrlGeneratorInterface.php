<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

/**
 * Interface UrlGeneratorInterface
 *
 * Contract for generating URLs from route names and parameters.
 */
interface UrlGeneratorInterface
{
    /**
     * Generate a URL for a named route with optional parameters.
     *
     * @param string $routeName
     * @param array<string, string|int> $params
     * @return string
     */
    public function generate(string $routeName, array $params = []): string;
}
