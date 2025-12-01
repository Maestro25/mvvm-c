<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class BaseMiddleware
 *
 * Abstract base class for PSR-15 compatible middleware.
 * Implements basic pass-through processing.
 */
abstract class Middleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Pre-processing logic can be inserted here by child classes

        // Delegate to the next middleware handler
        $response = $handler->handle($request);

        // Post-processing logic can be inserted here by child classes

        return $response;
    }
}
