<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Events\Interfaces\DomainEventInterface;
use App\Presentation\Shared\ViewModels\ViewModel;
use App\Presentation\ViewModels\Enums\PageState;

/**
 * Event dispatched when ViewModel validation completes successfully.
 */
final class ViewModelValidatedSuccessEvent extends DomainEvent
{
    public function __construct(
        private readonly ViewModel $viewModel
    ) {}

    public function getViewModel(): ViewModel
    {
        return $this->viewModel;
    }
}
