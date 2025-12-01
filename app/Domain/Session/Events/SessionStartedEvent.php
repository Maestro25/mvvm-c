<?php
declare(strict_types=1);

namespace App\Domain\Session\Events;


use App\Domain\Session\Enums\SessionState;
use Symfony\Contracts\EventDispatcher\Event;

final class SessionStartedEvent extends Event
{
    public function __construct(private SessionState $state) {}

    public function getState(): SessionState
    {
        return $this->state;
    }
}
