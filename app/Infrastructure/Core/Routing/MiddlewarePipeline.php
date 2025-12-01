<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class MiddlewarePipeline implements RequestHandlerInterface
{
    protected array $middlewareStack = [];


    public function __construct(private LoggerInterface $logger)
    {
    }

    public function addMiddleware(MiddlewareInterface $middleware): void
    {
        $this->logger->info(
            'Middleware added to pipeline: ',
            [
                'class' => get_class($middleware),
                'count' => count($this->middlewareStack) + 1,
            ]
        );
        $this->middlewareStack[] = $middleware;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info(
            'MiddlewarePipeline start:',
            [
                'count' => count($this->middlewareStack),
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
            ]
        );
        $handler = $this->createHandler(0);
        $response = $handler->handle($request);
        $this->logger->info('MiddlewarePipeline finished handling');
        return $response;
    }

    public function createHandler(int $index): RequestHandlerInterface
    {
        $logger = $this->logger;
        return new class ($this, $index, $logger) implements RequestHandlerInterface {
            private MiddlewarePipeline $pipeline;
            private int $index;
            private LoggerInterface $logger;

            public function __construct(MiddlewarePipeline $pipeline, int $index, LoggerInterface $logger)
            {
                $this->pipeline = $pipeline;
                $this->index = $index;
                $this->logger = $logger;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $middleware = $this->pipeline->getMiddlewareAt($this->index);
                if ($middleware === null) {
                    $this->logger->error('Middleware pipeline ended without response: ', ['index' => $this->index]);
                    throw new RuntimeException('End of middleware pipeline reached without response.');
                }

                $this->logger->info(
                    'Invoking middleware: ',
                    [
                        'class' => get_class($middleware),
                        'index' => $this->index,
                    ]
                );

                $nextHandler = ($this->pipeline->getMiddlewareAt($this->index + 1) !== null)
                    ? $this->pipeline->createHandler($this->index + 1)
                    : new class implements RequestHandlerInterface {
                    public function handle(ServerRequestInterface $request): ResponseInterface
                    {
                        throw new RuntimeException('No middleware handled the request.');
                    }
                    };

                return $middleware->process($request, $nextHandler);
            }
        };
    }



    public function getMiddlewareAt(int $index): ?MiddlewareInterface
    {
        return $this->middlewareStack[$index] ?? null;
    }
}
