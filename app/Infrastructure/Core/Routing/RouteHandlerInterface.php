<?php
declare(strict_types=1);

namespace App\Infrastructure\Core\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Interface RouteHandlerInterface
 *
 * Represents a handler that processes a ServerRequest and produces a Response,
 * adhering to PSR-15 RequestHandlerInterface contract.
 */
interface RouteHandlerInterface extends RequestHandlerInterface
{
    // No additional methods; directly extends PSR-15 RequestHandlerInterface.
}
