<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class LoggingMiddleware extends Middleware
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->info('Handling request', ['uri' => (string)$request->getUri()]);
        $response = $handler->handle($request);
        $this->logger->info('Response generated', ['status' => $response->getStatusCode()]);

        return $response;
    }
}
