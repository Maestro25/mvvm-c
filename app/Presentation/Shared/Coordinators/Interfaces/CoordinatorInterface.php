<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators\Interfaces;

use App\Presentation\ViewModels\ViewModel;
use App\Presentation\Observers\Interfaces\ObserverInterface;

/**
 * Interface for Coordinators in MVVM-C architecture.
 *
 * Defines contract for starting flows and managing lifecycle along with
 * attaching/detaching observers to ViewModels for reactive flow handling.
 */
interface CoordinatorInterface
{
    /**
     * Start the coordinator flow.
     */
    public function start(): void;

    /**
     * Add a child coordinator to this coordinator.
     *
     * @param CoordinatorInterface $coordinator
     */
    public function addChild(CoordinatorInterface $coordinator): void;

    /**
     * Remove a child coordinator from this coordinator.
     *
     * @param CoordinatorInterface $coordinator
     */
    public function removeChild(CoordinatorInterface $coordinator): void;

    /**
     * Attach an observer to a ViewModel with optional priority.
     *
     * @param ViewModel $viewModel
     * @param ObserverInterface|callable $observer
     * @param int $priority
     */
    public function attachViewModelObserver(ViewModel $viewModel, ObserverInterface|callable $observer, int $priority = 0): void;

    /**
     * Detach an observer from a ViewModel.
     *
     * @param ViewModel $viewModel
     * @param ObserverInterface|callable $observer
     */
    public function detachViewModelObserver(ViewModel $viewModel, ObserverInterface|callable $observer): void;

    /**
     * Perform cleanup, including removing observers and child coordinators.
     */
    public function onStop(): void;
}
