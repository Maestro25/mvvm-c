<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Nyholm\Psr7\Factory\Psr17Factory;

final class StaticAssetsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private string $publicPath
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Only handle GET requests
        if ($method !== 'GET') {
            $this->logger->debug('StaticAssetsMiddleware: Non-GET request', ['path' => $path]);
            return $handler->handle($request);
        }

        // Handle favicon.ico at root
        if ($path === '/favicon.ico') {
            return $this->serveFile('assets/favicon.ico', $request);
        }

        // Handle /assets/{path:.*}
        if (str_starts_with($path, '/assets/')) {
            $assetPath = substr($path, 8); // Remove /assets/ prefix
            return $this->serveFile($assetPath, $request);
        }

        // Pass through to next handler
        return $handler->handle($request);
    }

    private function serveFile(string $filePath, ServerRequestInterface $request): ResponseInterface
    {
        $fullPath = $this->publicPath . '/' . $filePath;

        // ✅ COMPREHENSIVE PATH LOGGING
        $this->logger->info('StaticAssetsMiddleware: Path Debug', [
            'publicPath' => $this->publicPath,
            'filePath' => $filePath,
            'fullPath' => $fullPath,
            'publicPath_exists' => is_dir($this->publicPath),
            'publicPath_realpath' => realpath($this->publicPath),
            'fullPath_exists' => file_exists($fullPath),
            'fullPath_is_file' => is_file($fullPath),
            'fullPath_realpath_attempt' => realpath($fullPath) ?: 'FALSE',
        ]);

        // Security: Block directory traversal (basic check first)
        if (str_contains($fullPath, '..')) {
            $this->logger->warning('StaticAssetsMiddleware: Directory traversal detected', ['fullPath' => $fullPath]);
            return (new Psr17Factory())->createResponse(403);
        }

        // ✅ SAFE realpath() handling with logging
        $realFullPath = realpath($fullPath);
        $realPublicPath = realpath($this->publicPath);

        $this->logger->info('StaticAssetsMiddleware: realpath results', [
            'realFullPath' => $realFullPath === false ? 'FALSE' : $realFullPath,
            'realPublicPath' => $realPublicPath === false ? 'FALSE' : $realPublicPath,
            'realFullPath_type' => gettype($realFullPath),
            'realPublicPath_type' => gettype($realPublicPath),
        ]);

        // Handle realpath failures
        if ($realFullPath === false || $realPublicPath === false) {
            $this->logger->warning('StaticAssetsMiddleware: realpath failed', [
                'fullPath' => $fullPath,
                'publicPath' => $this->publicPath,
            ]);
            return (new Psr17Factory())->createResponse(404);
        }

        // ✅ NOW SAFE to use str_starts_with
        if (!str_starts_with($realFullPath, $realPublicPath)) {
            $this->logger->warning('StaticAssetsMiddleware: Path outside public dir', [
                'realFullPath' => $realFullPath,
                'realPublicPath' => $realPublicPath,
            ]);
            return (new Psr17Factory())->createResponse(403);
        }

        if (!is_file($fullPath)) {
            $this->logger->info('StaticAssetsMiddleware: File not found', ['fullPath' => $fullPath]);
            return (new Psr17Factory())->createResponse(404);
        }

        return $this->createFileResponse($fullPath, $request);
    }


    private function createFileResponse(string $filePath, ServerRequestInterface $request): ResponseInterface
    {
        $factory = new Psr17Factory();
        $response = $factory->createResponse(200);

        $fileSize = filesize($filePath);
        $lastModified = filemtime($filePath);
        $mimeType = $this->getMimeType(pathinfo($filePath, PATHINFO_EXTENSION));

        $etag = md5_file($filePath);
        $response = $response
            ->withHeader('Content-Type', $mimeType)
            ->withHeader('Content-Length', (string) $fileSize)
            ->withHeader('ETag', $etag)
            ->withHeader('Cache-Control', 'public, max-age=31536000, immutable');

        // Check If-None-Match

        $headerLine = $request->getHeaderLine('If-None-Match');
        if (!empty($headerLine) && trim($headerLine, '"') === $etag) {
            return $response->withStatus(304);
        }
        // Check If-Modified-Since
        $ifModifiedSince = $request->getHeaderLine('If-Modified-Since');
        if ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) {
            return $response->withStatus(304);
        }

        $response = $response->withHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');

        $stream = fopen($filePath, 'rb');
        if ($stream === false) {
            return $factory->createResponse(500);
        }

        return $response->withBody($factory->createStreamFromResource($stream));
    }

    private function getMimeType(string $extension): string
    {
        return match ($extension) {
            'ico' => 'image/x-icon',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'css' => 'text/css',
            'js' => 'application/javascript',
            default => 'application/octet-stream'
        };
    }
}
