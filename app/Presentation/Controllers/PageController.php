<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Presentation\Coordinators\AppCoordinator;

/**
 * PageController serves as the HTTP adapter for UI page navigation.
 *
 * Responsibilities:
 * - Parse route attributes from HTTP requests.
 * - Delegate navigation commands to AppCoordinator.
 * - Return PSR-7 responses from coordinators.
 */
final class PageController
{
    private AppCoordinator $appCoordinator;

    public function __construct(AppCoordinator $appCoordinator)
    {
        $this->appCoordinator = $appCoordinator;
    }

    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $page = $request->getAttribute('page', null);

        // Treat null or empty as 'main'
        if ($page === null || $page === '') {
            $page = 'main';
        }

        $allowedPages = ['main', 'login','register', 'dashboard'];

        // Fallback to main for unknown pages
        if (!in_array($page, $allowedPages, true)) {
            $page = 'main';
        }

        return $this->appCoordinator->navigate($page);
    }
}

