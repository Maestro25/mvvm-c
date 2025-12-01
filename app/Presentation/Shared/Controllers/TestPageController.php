<?php
declare(strict_types=1);

namespace App\Presentation\Shared\Controllers;

use App\Presentation\Shared\Coordinators\TestPageCoordinator;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class TestPageController
{
    public function __construct(
        private TestPageCoordinator $coordinator
    ) {}

    /**
     * Handles an HTTP request, delegates to coordinator, and returns the response.
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        // Delegate flow control and rendering to the TestPageCoordinator
        return $this->coordinator->start($request);
    }
}
