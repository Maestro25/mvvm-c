<?php
declare(strict_types=1);

namespace App\Factories;

use Psr\Log\LoggerInterface;
use App\Presentation\Coordinators\AppCoordinator;
use App\Presentation\Coordinators\AuthCoordinator;
use App\Presentation\Coordinators\MainCoordinator;
use App\Presentation\ViewModels\Interfaces\AuthViewModelInterface;
use App\Presentation\ViewModels\Interfaces\MainViewModelInterface;
use App\Infrastructure\Core\Routing\RouteCollectionInterface;
use App\Presentation\Coordinators\AppCoordinatorInterface;
use App\Presentation\Coordinators\Interfaces\AuthCoordinatorInterface;

/**
 * Factory to create coordinator instances with their required dependencies.
 * Centralizes creation to ensure consistency and facilitate testing.
 */
final class CoordinatorFactory
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly RouteCollectionInterface $routeCollection,
        private readonly AuthViewModelInterface $authViewModel,
        private readonly MainViewModelInterface $mainViewModel,
    ) {}

    /**
     * Create the root AppCoordinator.
     */
    public function createAppCoordinator(): AppCoordinatorInterface
    {
        return new AppCoordinator(
            $this->authViewModel,
            $this->mainViewModel,
            $this->logger,
            $this->routeCollection
        );
    }

    /**
     * Create an AuthCoordinator.
     */
    public function createAuthCoordinator(): AuthCoordinatorInterface
    {
        // AuthController and related dependencies can be injected here as needed
        // For simplicity omitted in this snippet
        return new AuthCoordinator(
            $this->authViewModel,
            $this->authController,
            $this->logger,
            $this->routeCollection
        );
    }

    /**
     * Create a MainCoordinator.
     */
    public function createMainCoordinator(): MainCoordinatorInterface
    {
        return new MainCoordinator(
            $this->mainViewModel,
            $this->authViewModel,
            $this->logger,
            $this->routeCollection
        );
    }
}
