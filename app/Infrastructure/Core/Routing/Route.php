<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use App\Infrastructure\Core\Routing\Enums\HttpMethod;
use Psr\Http\Server\MiddlewareInterface;

final class Route implements RouteInterface
{
    private array $middlewares = [];

    public function __construct(
        private string $name,
        private HttpMethod $method,
        private string $path,
        private RouteHandlerInterface $handler,
        array $middlewares = []
    ) {
        $this->middlewares = $middlewares;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethod(): HttpMethod
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): RouteHandlerInterface
    {
        return $this->handler;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }
}
