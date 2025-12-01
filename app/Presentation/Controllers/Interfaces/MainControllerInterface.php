<?php
declare(strict_types=1);

namespace App\Presentation\Controllers\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for MainController.
 *
 * Defines entry points for main application flows,
 * session state checks, and informational endpoints.
 */
interface MainControllerInterface
{
    /**
     * Display main application page with user session information.
     */
    public function showMainPage(ServerRequestInterface $request): ResponseInterface;

    /**
     * Check if the current user is authenticated.
     */
    public function isAuthenticated(ServerRequestInterface $request): ResponseInterface;

    /**
     * Log out the current user.
     */
    public function logout(ServerRequestInterface $request): ResponseInterface;

    /**
     * Add a flash message to the user's session.
     */
    public function addFlashMessage(ServerRequestInterface $request): ResponseInterface;

    /**
     * Return application informational status.
     */
    public function info(ServerRequestInterface $request): ResponseInterface;

    /**
     * Return system health and status check.
     */
    public function status(ServerRequestInterface $request): ResponseInterface;
}
