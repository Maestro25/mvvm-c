<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use Psr\Log\LoggerInterface;
use Laminas\Diactoros\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use JsonException;
use InvalidArgumentException;

abstract class Controller
{
    protected LoggerInterface $logger;
    protected ResponseFactory $responseFactory;

    public function __construct(LoggerInterface $logger, ResponseFactory $responseFactory)
    {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Create a JSON response with given data and HTTP status.
     *
     * @param array<string, mixed> $data
     * @param int $statusCode
     * @return ResponseInterface
     * @throws JsonException
     */
    protected function createJsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Create a standardized JSON error response.
     *
     * @param string $message
     * @param int $statusCode
     * @return ResponseInterface
     * @throws JsonException
     */
    protected function createErrorJsonResponse(string $message, int $statusCode): ResponseInterface
    {
        $this->logger->warning("Error response ({$statusCode}): {$message}");
        return $this->createJsonResponse(['error' => $message, 'code' => $statusCode], $statusCode);
    }

    /**
     * Log exception and return standardized error response.
     *
     * @param string $message
     * @param \Throwable $exception
     * @param int $statusCode
     * @param 'error'|'warning'|'info'|'debug' $level
     * @return ResponseInterface
     * @throws JsonException
     */
    protected function handleException(
        string $message,
        \Throwable $exception,
        int $statusCode = 500,
        string $level = 'error'
    ): ResponseInterface {
        $validLevels = ['error', 'warning', 'info', 'debug'];
        if (!in_array($level, $validLevels, true)) {
            throw new InvalidArgumentException("Logger level '{$level}' is invalid.");
        }

        $this->logger->{$level}($message, ['exception' => $exception]);

        return $this->createErrorJsonResponse($message, $statusCode);
    }
}
