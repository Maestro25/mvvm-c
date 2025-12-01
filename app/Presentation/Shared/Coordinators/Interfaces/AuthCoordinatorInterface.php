<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators\Interfaces;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface AuthCoordinatorInterface
 *
 * Defines the public contract for the AuthCoordinator,
 * responsible for authentication flow coordination and navigation.
 */
interface AuthCoordinatorInterface
{
    /**
     * Starts the coordinator by attaching observers and initiating navigation.
     *
     * @return ResponseInterface PSR-7 response, typically a redirect to login page.
     */
    public function start(): ResponseInterface;

    /**
     * Navigate to the login screen UI.
     *
     * @return ResponseInterface PSR-7 redirect response to login page.
     */
    public function navigateToLogin(): ResponseInterface;

    /**
     * Navigate to the registration screen UI.
     *
     * @return ResponseInterface PSR-7 redirect response to registration page.
     */
    public function navigateToRegistration(): ResponseInterface;

    /**
     * Stop the coordinator, performing cleanup such as detaching observers.
     */
    public function stop(): void;
}
