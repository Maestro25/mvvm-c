<?php

declare(strict_types=1);

namespace App\Domain\Shared\Events;

use App\Presentation\Shared\ViewModels\Enums\PageState;
use Symfony\Contracts\EventDispatcher\Event;


final class PageStateInitializedEvent extends Event
{
    public function __construct(private PageState $initialState) {}

    public function getInitialState(): PageState
    {
        return $this->initialState;
    }
}
