<?php
declare(strict_types=1);

namespace App\Presentation\Shared\ViewModels\Listeners;

use App\Application\Shared\Events\PageStateChangeCompletedEvent;
use App\Presentation\Shared\ViewModels\Enums\PageState;
use App\Presentation\Shared\Views\ViewRendererInterface;
use Psr\Log\LoggerInterface;
use App\Presentation\Shared\Vms\Renderer\PageRendererInterface;

final class PageStateObserver
{
    private PageState $currentState;

    public function __construct(
        private ViewRendererInterface $renderer,
        private LoggerInterface $logger
    ) {
        $this->currentState = PageState::INITIAL;
    }

    public function onPageStateChangeCompleted(PageStateChangeCompletedEvent $event): void
    {
        $newState = $event->getFinalState();

        $this->logger->info(sprintf('PageStateObserver: transitioning from %s to %s', $this->currentState->value, $newState->value));

        // Update internal state
        $this->currentState = $newState;

        // React and delegate rendering as per state
        switch ($newState) {
            case PageState::LOADING:
                $this->renderer->renderLoading();
                break;

            case PageState::LOADED:
                $this->renderer->renderContent();
                break;

            case PageState::ERROR:
                $errorDetails = $this->fetchErrorDetails();
                $this->renderer->renderErrorTemplate($errorDetails);
                break;

            default:
                $this->logger->info('PageStateObserver: no special rendering for this state');
                $this->renderer->renderDefault();
                break;
        }
    }

    private function fetchErrorDetails(): array
    {
        // Implement fetching error info as needed
        return [
            'message' => 'An unexpected error occurred.',
            'code' => 500,
        ];
    }

    public function getCurrentState(): PageState
    {
        return $this->currentState;
    }
}
