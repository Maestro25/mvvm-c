<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class AuthenticationMiddleware extends Middleware
{
    private ResponseFactoryInterface $responseFactory;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user'); // Assume user info stored here after session auth
        if ($user === null) {
            $response = $this->responseFactory->createResponse(401);
            $response->getBody()->write('Unauthorized');
            return $response;
        }

        return $handler->handle($request);
    }
}
