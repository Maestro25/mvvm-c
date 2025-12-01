<?php
declare(strict_types=1);

namespace App\Presentation\Shared\Coordinators;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Log\LoggerInterface;

final class TestCoordinator
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Start processing the HTTP request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function start(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('TestCoordinator start method invoked', [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
        ]);

        // Simple response for test route
        return new HtmlResponse('<h1>TestCoordinator response: Route logic works!</h1>');
    }
}
