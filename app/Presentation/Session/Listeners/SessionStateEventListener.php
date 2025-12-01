<?php
declare(strict_types=1);

namespace App\Presentation\Session\Listeners;

use App\Domain\Session\Events\SessionStartedEvent;
use App\Domain\Session\Events\SessionRegeneratedEvent;
use App\Domain\Session\Events\SessionDestroyedEvent;
use App\Domain\Session\Events\SessionExpiredEvent;
use App\Domain\Session\Events\SessionFailedEvent;
use App\Domain\Session\Events\SessionPausedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class SessionStateEventListener implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SessionStartedEvent::class => 'onSessionStarted',
            SessionRegeneratedEvent::class => 'onSessionRegenerated',
            SessionDestroyedEvent::class => 'onSessionDestroyed',
            SessionExpiredEvent::class => 'onSessionExpired',
            SessionFailedEvent::class => 'onSessionFailed',
            SessionPausedEvent::class => 'onSessionPaused',
        ];
    }

    public function onSessionStarted(SessionStartedEvent $event): void
    {
        $this->logger->info('Session started with state: ' . $event->getState()->value);
        // Initialization and security logic here
    }

    public function onSessionRegenerated(SessionRegeneratedEvent $event): void
    {
        $this->logger->info('Session ID regenerated: ' . $event->getState()->value);
        // Handle session fixation protection logic here
    }

    public function onSessionDestroyed(SessionDestroyedEvent $event): void
    {
        $this->logger->info('Session destroyed: ' . $event->getState()->value);
        // Cleanup resources or audit logs here
    }

    public function onSessionExpired(SessionExpiredEvent $event): void
    {
        $this->logger->warning('Session expired: ' . $event->getState()->value);
        // Alert user or force re-login workflow
    }

    public function onSessionFailed(SessionFailedEvent $event): void
    {
        $this->logger->error('Session failed: ' . $event->getState()->value);
        // Handle session startup failures or errors
    }

    public function onSessionPaused(SessionPausedEvent $event): void
    {
        $this->logger->info('Session paused: ' . $event->getState()->value);
        // Temporary suspension handling if applicable
    }
}
