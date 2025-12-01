<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels\Events;

use App\Domain\Events\DomainEvent;
use App\Domain\Events\Interfaces\DomainEventInterface;
use App\Presentation\Shared\ViewModels\ViewModel;
use App\Presentation\ViewModels\Enums\PageState;

/**
 * Event dispatched when ViewModel validation fails.
 */
final class ViewModelValidatedFailureEvent extends DomainEvent
{   /** @var ViewModel */
    private ViewModel $viewModel;
    /** @var array<string, string[]> Validation errors */
    private array $validationErrors;

    /**
     * @param ViewModel $viewModel
     * @param array<string, string[]> $validationErrors
     */
    public function __construct(ViewModel $viewModel, array $validationErrors)
    {
        $this->viewModel = $viewModel;
        $this->validationErrors = $validationErrors;
    }

    public function getViewModel(): ViewModel
    {
        return $this->viewModel;
    }

    /**
     * Returns validation errors grouped by field.
     *
     * @return array<string, string[]>
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
