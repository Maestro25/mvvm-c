<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

final class NotFoundMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private Psr17Factory $psr17Factory
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            // Try next middleware/handler (router)
            return $handler->handle($request);
        } catch (\Exception $e) {
            $path = $request->getUri()->getPath();
            $this->logger->warning('Route not found, serving 404', [
                'path' => $path,
                'method' => $request->getMethod(),
                'exception' => $e->getMessage()
            ]);

            $html = $this->render404($path);
            
            return $this->psr17Factory->createResponse(404)
                ->withHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->psr17Factory->createStream($html));
        }
    }

    private function render404(string $path): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>404 - Not Found</title>
    <meta charset="utf-8">
</head>
<body style="font-family: system-ui; padding: 2rem; max-width: 600px; margin: 0 auto;">
    <h1>404 - Page Not Found</h1>
    <p>The requested path de>{$path}</code> does not exist.</p>
    <p><a href="/test">Go to Test Page →</a></p>
</body>
</html>
HTML;
    }
}
