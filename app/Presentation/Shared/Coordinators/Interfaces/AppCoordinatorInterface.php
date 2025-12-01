<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators;

use App\Presentation\Coordinators\Interfaces\CoordinatorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for AppCoordinator.
 * Root coordinator managing entire app flow.
 */
interface AppCoordinatorInterface extends CoordinatorInterface
{
    /**
     * Callback triggered on successful login, transitioning to main app.
     *
     * @param string $userId
     * @return ResponseInterface
     */
    public function onLoginSuccess(string $userId): ResponseInterface;
}
