<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Events\Interfaces\DomainEventInterface;
use App\Presentation\Shared\ViewModels\Enums\PageState;
use App\Presentation\Shared\ViewModels\ViewModel;

/**
 * Event dispatched when the ViewModel page state changes.
 */
final class ViewModelPageStateChangedEvent extends DomainEvent
{
    public function __construct(
        private readonly ViewModel $viewModel,
        private readonly PageState $newState
    ) {}

    public function getViewModel(): ViewModel
    {
        return $this->viewModel;
    }

    public function getNewState(): PageState
    {
        return $this->newState;
    }
}

