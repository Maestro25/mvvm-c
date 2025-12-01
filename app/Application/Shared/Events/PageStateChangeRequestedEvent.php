<?php

declare(strict_types=1);

namespace App\Application\Shared\Events;

use App\Presentation\Shared\ViewModels\Enums\PageState;
use Symfony\Contracts\EventDispatcher\Event;

final class PageStateChangeRequestedEvent extends Event
{
    public function __construct(private PageState $requestedState) {}

    public function getRequestedState(): PageState
    {
        return $this->requestedState;
    }
}


