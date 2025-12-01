<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use App\Config\SessionConfig;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Application\Session\Services\SessionService;
use Psr\Log\LoggerInterface;
use App\Domain\Shared\Validation\Exceptions\ValidationException;
use Laminas\Diactoros\Response\HtmlResponse;

final class SessionMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Handles the incoming HTTP request and manages session lifecycle.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->logger->info('SessionMiddleware: Starting session processing');

        try {
            SessionConfig::apply();
            $this->sessionService->startSession();

        } catch (ValidationException $ve) {
            // Log detailed validation errors with source context
            $this->logger->error('SessionMiddleware: Validation failed during session start', [
                'exception_message' => $ve->getMessage(),
                'errors' => $ve->getErrors(),
                'source_context' => $ve->getSourceContext(),
            ]);

            // Return a 400 Bad Request or similar response with error details (for dev/testing)
            return new HtmlResponse(
                '<h1>Validation Error</h1><pre>' .
                htmlspecialchars(print_r($ve->getErrors(), true), ENT_QUOTES | ENT_HTML5) .
                '</pre>',
                400
            );

        } catch (\Throwable $e) {
            $this->logger->error('SessionMiddleware: Failed to start session', ['exception' => $e]);
            // Optionally handle this more gracefully or return a 500 response if critical
            throw $e;
        }

        $this->logger->info('SessionMiddleware: PHP session started successfully', [
            'session_id' => session_id(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'guest_id' => $_SESSION['guest_id'] ?? null,
        ]);

        // Pass control to next middleware or request handler
        $response = $handler->handle($request);

        // Optionally, perform any session cleanup or finalization here if needed

        $this->logger->info('SessionMiddleware: Request processed, returning response', [
            'response_status' => $response->getStatusCode(),
            'session_id' => session_id(),
        ]);

        return $response;
    }
}
