<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Presentation\Shared\ViewModels\Enums\PageState;
use App\Application\Shared\Events\PageStateChangeRequestedEvent;
use App\Application\Shared\Events\PageStateChangeCompletedEvent;
use App\Presentation\Shared\Data\SanitizerInterface;

/**
 * BaseViewModel for MVVM-C
 * - Handles page state management and event dispatch using Symfony EventDispatcher.
 * - Relies on external DTO-to-ViewModel mapper.
 * - Integrates input sanitization as service.
 */
abstract class BaseViewModel
{
    protected PageState $currentState;
    protected mixed $rawData = null;
    protected mixed $viewData = null;

    public function __construct(
        protected LoggerInterface $logger,
        protected EventDispatcherInterface $eventDispatcher,
        protected SanitizerInterface $sanitizer,
        // Inject mapper interface for DTO-to-VM conversion
        protected ViewModelMapperInterface $mapper
    ) {
        $this->currentState = PageState::INITIAL;
    }

    public function getCurrentState(): PageState
    {
        return $this->currentState;
    }

    /**
     * Receive raw input, sanitize it, then use mapper to convert DTO to ViewModel data.
     */
    public function setRawData(array $rawData): void
    {
        $this->logger->debug('Sanitizing raw data for ViewModel');
        $sanitized = $this->sanitizeData($rawData);
        $this->rawData = $sanitized;

        $this->logger->debug('Mapping sanitized DTO to ViewModel');
        $this->viewData = $this->mapper->toViewModel($sanitized);
        $this->prepareDataForView();
    }

    /**
     * Recursive sanitizer helper.
     */
    protected function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $this->sanitizer->sanitize($value);
            }
        }
        return $sanitized;
    }

    /**
     * Prepare data further if needed. Override in concrete VMs.
     */
    protected function prepareDataForView(): void
    {
        // Default noop, allow VM-specific formatting
    }

    /**
     * Return view-prepared data.
     */
    public function getViewData(): mixed
    {
        return $this->viewData;
    }

    /**
     * Dispatch page state change events.
     */
    public function requestStateChange(PageState $newState): void
    {
        if ($this->currentState === $newState) {
            $this->logger->info('Page state unchanged; skipping.');
            return;
        }

        $this->logger->info(sprintf(
            'Requesting page state change from %s to %s',
            $this->currentState->value,
            $newState->value
        ));

        $this->eventDispatcher->dispatch(new PageStateChangeRequestedEvent($newState));
        $this->currentState = $newState;
        $this->eventDispatcher->dispatch(new PageStateChangeCompletedEvent($newState));
    }

    /**
     * Reset ViewModel state.
     */
    public function reset(): void
    {
        $this->logger->info('Resetting ViewModel state to initial state');
        $this->requestStateChange(PageState::INITIAL);
    }
}
