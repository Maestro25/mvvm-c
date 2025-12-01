<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthorizationMiddleware extends Middleware
{
    private ResponseFactoryInterface $responseFactory;
    private array $allowedRoles;

    public function __construct(ResponseFactoryInterface $responseFactory, array $allowedRoles)
    {
        $this->responseFactory = $responseFactory;
        $this->allowedRoles = $allowedRoles;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');
        if ($user === null || !in_array($user->getRole(), $this->allowedRoles, true)) {
            $response = $this->responseFactory->createResponse(403);
            $response->getBody()->write('Forbidden');
            return $response;
        }

        return $handler->handle($request);
    }
}
