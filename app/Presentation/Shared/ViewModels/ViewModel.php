<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels;

use App\Presentation\Shared\ViewModels\Enums\PageState;
use App\Application\Shared\Events\{
    PageStateChangeRequestedEvent,
    PageStateChangeCompletedEvent,

};
use App\Domain\Shared\Events\PageStateInitializedEvent;
use App\Presentation\Shared\Events\PageStateUpdatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * BaseViewModel for MVVM-C Architecture
 * Includes:
 * - Page state management
 * - Observer pattern support
 * - Hydration & validation contracts (to be implemented by subclasses)
 * - Flash message queue and notification logic
 * - Event dispatching with error handling
 */
abstract class ViewModel
{
    protected PageState $pageState;
    private array $observers = [];
    protected array $validationErrors = [];
    private array $flashMessages = [];

    public function __construct(
        protected LoggerInterface $logger,
        protected EventDispatcherInterface $eventDispatcher,
        ?PageState $initialState = null
    ) {
        $this->pageState = $initialState ?? PageState::INITIAL;
        $this->dispatchEvent(new PageStateInitializedEvent($this->pageState));
        $this->logger->info(sprintf('VM initialized with state %s', $this->pageState->value));
    }

    public function addObserver(callable $observer): void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(callable $observer): void
    {
        $this->observers = array_filter($this->observers, fn($o) => $o !== $observer);
    }

    protected function notifyObservers(): void
    {
        foreach ($this->observers as $observer) {
            try {
                $observer($this->pageState, $this);
            } catch (\Throwable $e) {
                $this->logger->error('Observer callback error: ' . $e->getMessage());
            }
        }
    }

    public function getPageState(): PageState
    {
        return $this->pageState;
    }

    public function setPageState(PageState $newState): void
    {
        if ($newState === $this->pageState) return;

        $this->logger->info(sprintf('Page state change requested: %s -> %s', $this->pageState->value, $newState->value));
        $this->dispatchEvent(new PageStateChangeRequestedEvent($this->pageState, $newState));

        $previousState = $this->pageState;
        $this->pageState = $newState;

        $this->dispatchEvent(new PageStateChangeCompletedEvent($previousState, $newState));
        $this->dispatchEvent(new PageStateUpdatedEvent($previousState, $newState));

        $this->logger->info(sprintf('Page state updated to %s', $newState->value));
        $this->notifyObservers();
    }

    /**
     * Hydrate the ViewModel with data, ensure sanitization occurs externally
     * @param array<string, mixed> $data
     */
    abstract public function hydrate(array $data): void;

    /**
     * Validate hydrated data, populate validationErrors
     * @return bool True if valid, false otherwise
     */
    abstract public function validate(): bool;

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function reset(): void
    {
        $this->validationErrors = [];
        $this->flashMessages = [];
        $this->setPageState(PageState::INITIAL);
        $this->logger->info('VM state reset');
        $this->notifyObservers();
    }

    // Flash messages & notifications

    /**
     * Add a flash message
     *
     * @param string $message
     * @param string $category E.g. 'info', 'success', 'warning', 'error'
     */
    public function addFlashMessage(string $message, string $category = 'info'): void
    {
        $this->flashMessages[$category][] = $message;
        $this->logger->info(sprintf('Flash message added [%s]: %s', $category, $message));
        $this->notifyObservers();
    }

    /**
     * Retrieve and clear all flash messages
     *
     * @return array<string, string[]> Messages by category
     */
    public function consumeFlashMessages(): array
    {
        $messages = $this->flashMessages;
        $this->flashMessages = [];
        return $messages;
    }

    protected function dispatchEvent(object $event): void
    {
        try {
            $this->eventDispatcher->dispatch($event);
        } catch (\Throwable $e) {
            $this->logger->error('Event dispatch error: ' . $e->getMessage());
        }
    }
}
