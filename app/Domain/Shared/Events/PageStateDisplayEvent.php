<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events;

use App\Presentation\Shared\ViewModels\Enums\PageState;
use Symfony\Contracts\EventDispatcher\Event;

final class PageStateDisplayEvent extends Event
{
    public function __construct(private PageState $displayState) {}

    public function getDisplayState(): PageState
    {
        return $this->displayState;
    }
}




