<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels\Listeners;

use App\Application\Shared\Events\PageStateChangeCompletedEvent;
use App\Application\Shared\Events\PageStateChangeRequestedEvent;
use App\Domain\Shared\Events\PageStateDisplayEvent;
use App\Domain\Shared\Events\PageStateInitializedEvent;
use App\Presentation\Shared\Events\PageStateResetEvent;
use App\Presentation\Shared\Events\PageStateUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;

final class PageStateEventListener implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageStateInitializedEvent::class => 'onPageStateInitialized',
            PageStateUpdatedEvent::class => 'onPageStateUpdated',
            PageStateChangeRequestedEvent::class => 'onPageStateChangeRequested',
            PageStateChangeCompletedEvent::class => 'onPageStateChangeCompleted',
            PageStateDisplayEvent::class => 'onPageStateDisplay',
            PageStateResetEvent::class => 'onPageStateReset',
        ];
    }

    public function onPageStateInitialized(PageStateInitializedEvent $event): void
    {
        $this->logger->info('Page state initialized: ' . $event->getInitialState()->value);
        // Handle initialization logic, e.g. set default UI state
    }

    public function onPageStateUpdated(PageStateUpdatedEvent $event): void
    {
        $this->logger->info(sprintf(
            'Page state changed from %s to %s',
            $event->getPreviousState()->value,
            $event->getCurrentState()->value
        ));
        // Update domain related logic based on state change
    }

    public function onPageStateChangeRequested(PageStateChangeRequestedEvent $event): void
    {
        $this->logger->info('Page state change requested to ' . $event->getRequestedState()->value);
        // Validate or trigger any pre-change workflow
    }

    public function onPageStateChangeCompleted(PageStateChangeCompletedEvent $event): void
    {
        $this->logger->info('Page state change completed: ' . $event->getFinalState()->value);
        // Trigger side effects after state transition, potentially dispatch display events
    }

    public function onPageStateDisplay(PageStateDisplayEvent $event): void
    {
        $this->logger->info('Displaying page state: ' . $event->getDisplayState()->value);
        // Notify UI layer or observers to update display accordingly
    }

    public function onPageStateReset(PageStateResetEvent $event): void
    {
        $this->logger->info('Page state reset to initial/default');
        // Clean up or reset UI elements and VM state
    }
}
