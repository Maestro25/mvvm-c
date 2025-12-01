<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * ExceptionHandlingMiddleware
 * 
 * Catches all exceptions thrown by downstream middleware or handlers,
 * logs the error, and returns a friendly HTTP error response.
 */
final class ExceptionHandlingMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private ResponseFactoryInterface $responseFactory;
    private bool $displayErrorDetails;

    public function __construct(
        LoggerInterface $logger,
        ResponseFactoryInterface $responseFactory,
        bool $displayErrorDetails = false
    ) {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $exception) {
            // Log exception with critical severity
            $this->logger->critical('Unhandled exception caught in middleware', [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $response = $this->responseFactory->createResponse(500);
            $response->getBody()->write(
                $this->formatErrorMessage($exception)
            );

            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        }
    }

    private function formatErrorMessage(Throwable $exception): string
    {
        if ($this->displayErrorDetails) {
            return sprintf(
                '<h1>Internal Server Error</h1><p>%s</p><pre>%s</pre>',
                htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($exception->getTraceAsString(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );
        }
        return '<h1>Internal Server Error</h1><p>An unexpected error occurred.</p>';
    }
}
