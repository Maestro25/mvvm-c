<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Server\MiddlewareInterface;
use App\Infrastructure\Core\Routing\Enums\HttpMethod;

interface RouteInterface
{
    public function getName(): string;

    public function getMethod(): HttpMethod;

    public function getPath(): string;

    public function getHandler(): RouteHandlerInterface;

    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array;

    /**
     * @param MiddlewareInterface[] $middlewares
     */
    public function setMiddlewares(array $middlewares): void;
    public function addMiddleware(MiddlewareInterface $middleware): void;
}
