<?php
declare(strict_types=1);

namespace App\Presentation\Session\Listeners;

use App\Domain\Session\Events\SessionStateChangeCompletedEvent;
use Psr\Log\LoggerInterface;

final class SessionStateObserver
{
    private $currentState;

    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function onSessionStateChangeCompleted(SessionStateChangeCompletedEvent $event): void
    {
        $newState = $event->getFinalState();

        $this->logger->info(sprintf('Session state transitioned to %s', $newState->value));

        // Implement reaction logic to state transition here
        // e.g. reset security tokens, notify subsystems, audit logs, etc.
    }
}
