<?php

declare(strict_types=1);

namespace App\Application\Shared\Events;

use App\Presentation\Shared\ViewModels\Enums\PageState;
use Symfony\Contracts\EventDispatcher\Event;

final class PageStateChangeCompletedEvent extends Event
{
    public function __construct(private PageState $finalState) {}

    public function getFinalState(): PageState
    {
        return $this->finalState;
    }
}



