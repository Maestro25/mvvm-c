<?php

declare(strict_types=1);

namespace App\Presentation\Shared\Events;

use App\Presentation\Shared\ViewModels\Enums\PageState;
use Symfony\Contracts\EventDispatcher\Event;

final class PageStateUpdatedEvent extends Event
{
    public function __construct(
        private PageState $previousState,
        private PageState $currentState
    ) {
    }

    public function getPreviousState(): PageState
    {
        return $this->previousState;
    }

    public function getCurrentState(): PageState
    {
        return $this->currentState;
    }
}

