<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators;

use App\Infrastructure\Core\Routing\RouteNames;
use Psr\Log\LoggerInterface;
use App\Presentation\ViewModels\Interfaces\ViewModelInterface;
use App\Presentation\Observers\Interfaces\ObserverInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Abstract base Coordinator class for MVVM-C.
 * Manages child coordinators, ViewModel observers, lifecycle and navigation delegation.
 */
abstract class Coordinator
{
    /** @var array<string, self> Child coordinators keyed by object hash */
    protected array $childCoordinators = [];

    /** @var array<string, ViewModelInterface> ViewModels keyed by object hash */
    protected array $viewModels = [];

    /**
     * @var array<string, ObserverInterface|callable>
     * Attached observers keyed by combination of ViewModel and observer hashes
     */
    protected array $viewModelObservers = [];

    protected LoggerInterface $logger;

    /**
     * RouteNames instance encapsulating allowed route constants or enums.
     * Helps enforce strong typing and consistency for navigation destinations.
     */
    protected RouteNames $routeNames;

    public function __construct(LoggerInterface $logger, RouteNames $routeNames)
    {
        $this->logger = $logger;
        $this->routeNames = $routeNames;
    }

    /**
     * Start the coordinator flow.
     */
    abstract public function start(): ResponseInterface;

    /**
     * Add a child coordinator to maintain lifecycle.
     */
    public function addChild(self $coordinator): void
    {
        $hash = spl_object_hash($coordinator);
        if (!isset($this->childCoordinators[$hash])) {
            $this->childCoordinators[$hash] = $coordinator;
        }
    }

    /**
     * Remove a child coordinator and stop it.
     */
    public function removeChild(self $coordinator): void
    {
        $hash = spl_object_hash($coordinator);
        if (isset($this->childCoordinators[$hash])) {
            $this->childCoordinators[$hash]->onStop();
            unset($this->childCoordinators[$hash]);
        }
    }

    /**
     * Check if a child coordinator exists by instance.
     */
    public function hasChild(self $coordinator): bool
    {
        return isset($this->childCoordinators[spl_object_hash($coordinator)]);
    }

    /**
     * Attach an observer to a ViewModel with priority.
     *
     * @param ViewModelInterface $viewModel
     * @param ObserverInterface|callable $observer
     * @param int $priority
     */
    protected function attachViewModelObserver(
        ViewModelInterface $viewModel,
        ObserverInterface|callable $observer,
        int $priority = 0
    ): void {
        $viewModel->attachObserver($observer, $priority);
        $key = $this->generateObserverKey($viewModel, $observer);

        $this->viewModelObservers[$key] = $observer;

        $viewModelHash = spl_object_hash($viewModel);
        if (!isset($this->viewModels[$viewModelHash])) {
            $this->viewModels[$viewModelHash] = $viewModel;
        }
    }

    /**
     * Detach an observer from a ViewModel.
     */
    protected function detachViewModelObserver(ViewModelInterface $viewModel, ObserverInterface|callable $observer): void
    {
        if ($viewModel->detachObserver($observer)) {
            $key = $this->generateObserverKey($viewModel, $observer);
            unset($this->viewModelObservers[$key]);
        }
    }

    /**
     * Remove all attached observers.
     */
    protected function clearViewModelObservers(): void
    {
        foreach ($this->viewModelObservers as $key => $observer) {
            [$viewModelHash] = explode(':', $key);
            if (isset($this->viewModels[$viewModelHash])) {
                try {
                    $this->viewModels[$viewModelHash]->detachObserver($observer);
                } catch (Throwable $e) {
                    $this->logError("Failed to detach observer during cleanup: {$e->getMessage()}", $e);
                }
            }
        }
        $this->viewModelObservers = [];
        $this->viewModels = [];
    }

    /**
     * Generate a unique key for ViewModel-observer pair.
     */
    private function generateObserverKey(ViewModelInterface $viewModel, ObserverInterface|callable $observer): string
    {
        $viewModelHash = spl_object_hash($viewModel);
        $observerHash = is_object($observer) ? spl_object_hash($observer) : spl_object_hash((object)$observer);
        return "{$viewModelHash}:{$observerHash}";
    }

    /**
     * Log errors with optional exception context.
     */
    protected function logError(string $message, ?Throwable $e = null): void
    {
        if ($e !== null) {
            $this->logger->error($message, ['exception' => $e]);
        } else {
            $this->logger->error($message);
        }
    }

    /**
     * Hook called when coordinator starts. Override if needed.
     */
    protected function onStart(): void
    {
        // Optional override for startup logic.
    }

    /**
     * Hook called when coordinator stops. Clears observers and stops all child coordinators.
     */
    protected function onStop(): void
    {
        $this->clearViewModelObservers();
        foreach ($this->childCoordinators as $child) {
            $child->onStop();
        }
        $this->childCoordinators = [];
    }

    /**
     * Navigation contract - subclasses implement by returning a response for destination.
     *
     * @param string $destination
     * @return ResponseInterface
     */
    abstract protected function navigate(string $destination): ResponseInterface;

    /**
     * Delegate routing/navigation to child coordinators if they support it.
     *
     * @param string $destination
     * @return ResponseInterface|null Return response if handled, null if no child handled it.
     */
    protected function delegateNavigationToChildren(string $destination): ?ResponseInterface
    {
        foreach ($this->childCoordinators as $child) {
            try {
                $response = $child->navigate($destination);
                if ($response instanceof ResponseInterface) {
                    return $response;
                }
            } catch (Throwable $e) {
                $this->logger->warning("Child coordinator navigation to '{$destination}' failed: {$e->getMessage()}");
            }
        }

        return null;
    }

    /**
     * Validates that the given route exists in the RouteNames set.
     * Throw or handle unknown routes explicitly.
     */
    protected function validateRoute(string $route): string
    {
        if (!in_array($route, $this->routeNames->getAllRoutes(), true)) {
            $this->logError("Navigation failed due to unknown route '{$route}'.");
            throw new \InvalidArgumentException("Unknown navigation route '{$route}'.");
        }
        return $route;
    }
}
