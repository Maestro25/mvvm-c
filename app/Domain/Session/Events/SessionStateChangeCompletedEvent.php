<?php
declare(strict_types=1);

namespace App\Domain\Session\Events;


use App\Domain\Session\Enums\SessionState;
use Symfony\Contracts\EventDispatcher\Event;

final class SessionStateChangeCompletedEvent extends Event
{
    public function __construct(private SessionState $finalState) {}

    public function getFinalState(): SessionState
    {
        return $this->finalState;
    }
}
