<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Presentation\Coordinators\AuthCoordinator;
use App\Presentation\Controllers\Interfaces\AuthControllerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthController extends Controller implements AuthControllerInterface
{
    private AuthCoordinator $authCoordinator;
    protected LoggerInterface $logger;

    public function __construct(
        AuthCoordinator $authCoordinator,
        LoggerInterface $logger,
        ResponseFactoryInterface $responseFactory
    ) {
        parent::__construct($logger, $responseFactory);
        $this->authCoordinator = $authCoordinator;
        $this->logger = $logger;
    }

    public function login(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $data = $this->parseRequestBody($request);
            // Return the response from coordinator directly (could be RedirectResponse)
            return $this->authCoordinator->login($data);
        } catch (\Throwable $e) {
            $this->logger->error('Login failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Login failed'], 401);
        }
    }

    public function register(ServerRequestInterface $request): ResponseInterface
{
    try {
        $data = $this->parseRequestBody($request);
        return $this->authCoordinator->register($data);
    } catch (\Throwable $e) {
        $this->logger->error('Registration failed: ' . $e->getMessage(), ['exception' => $e]);
        return new JsonResponse(['error' => 'Registration failed'], 400);
    }
}

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->authCoordinator->logout();
        } catch (\Throwable $e) {
            $this->logger->error('Logout failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Logout failed'], 500);
        }
    }

    public function currentUserId(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userId = $this->authCoordinator->getCurrentUserId();
            return new JsonResponse(['user_id' => $userId]);
        } catch (\Throwable $e) {
            $this->logger->error('CurrentUserId retrieval failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['user_id' => null], 200);
        }
    }

    public function generateCsrfToken(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $token = $this->authCoordinator->generateCsrfToken();
            return new JsonResponse(['csrf_token' => $token]);
        } catch (\Throwable $e) {
            $this->logger->error('CSRF token generation failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['error' => 'Unable to generate CSRF token'], 500);
        }
    }

    public function isAuthenticated(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $isAuthenticated = $this->authCoordinator->isAuthenticated();
            return new JsonResponse(['authenticated' => $isAuthenticated]);
        } catch (\Throwable $e) {
            $this->logger->error('Authentication check failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['authenticated' => false], 200);
        }
    }

    public function validateCsrfToken(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $params = $request->getParsedBody() ?? [];
            $token = $params['csrf_token'] ?? null;
            $isValid = $this->authCoordinator->validateCsrfToken($token);
            return new JsonResponse(['valid' => $isValid]);
        } catch (\Throwable $e) {
            $this->logger->error('CSRF token validation failed: ' . $e->getMessage(), ['exception' => $e]);
            return new JsonResponse(['valid' => false], 200);
        }
    }

    private function parseRequestBody(ServerRequestInterface $request): array
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        }
        return $request->getParsedBody() ?? [];
    }
}
