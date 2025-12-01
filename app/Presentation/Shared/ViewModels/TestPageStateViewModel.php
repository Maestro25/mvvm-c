<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels;

use App\Application\Shared\Events\PageStateChangeCompletedEvent;
use App\Application\Shared\Events\PageStateChangeRequestedEvent;
use App\Domain\Shared\Events\PageStateInitializedEvent;
use App\Presentation\Shared\Events\PageStateResetEvent;
use App\Presentation\Shared\Events\PageStateUpdatedEvent;
use App\Presentation\Shared\ViewModels\Enums\PageState;
use Psr\EventDispatcher\EventDispatcherInterface;

final class TestPageStateViewModel
{
    private PageState $currentState;

    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {
        $this->currentState = PageState::INITIAL;
        $this->eventDispatcher->dispatch(new PageStateInitializedEvent($this->currentState));
    }

    /**
     * Request changing the page state.
     * Dispatches events for requested, updated, and completed changes.
     *
     * @param PageState $newState
     */
    public function requestChange(PageState $newState): void
    {
        // Dispatch event for requested change
        $this->eventDispatcher->dispatch(new PageStateChangeRequestedEvent($newState));

        // Store old state for event
        $oldState = $this->currentState;

        // Update state
        $this->currentState = $newState;

        // Dispatch event for state updated
        $this->eventDispatcher->dispatch(new PageStateUpdatedEvent($oldState, $newState));

        // Dispatch event for change completed
        $this->eventDispatcher->dispatch(new PageStateChangeCompletedEvent($newState));
    }

    /**
     * Reset the page state to initial.
     */
    public function reset(): void
    {
        $this->currentState = PageState::INITIAL;
        $this->eventDispatcher->dispatch(new PageStateResetEvent());
    }

    /**
     * Get the current page state.
     */
    public function getCurrentState(): PageState
    {
        return $this->currentState;
    }
}
