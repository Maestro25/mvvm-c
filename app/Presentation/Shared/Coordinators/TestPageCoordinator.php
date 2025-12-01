<?php
declare(strict_types=1);

namespace App\Presentation\Shared\Coordinators;

use App\Application\Shared\Events\PageStateChangeCompletedEvent;
use App\Presentation\Shared\Events\PageStateResetEvent;
use App\Presentation\Shared\ViewModels\TestPageStateViewModel;
use App\Presentation\Shared\ViewModels\Enums\PageState;
use App\Presentation\Shared\Views\ViewRendererInterface;
use Psr\Log\LoggerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class TestPageCoordinator
{
    public function __construct(
        private TestPageStateViewModel $viewModel,
        private ViewRendererInterface $renderer,
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher
    ) {
        $this->registerEventListeners();
    }

    private function registerEventListeners(): void
    {
        if (method_exists($this->eventDispatcher, 'addListener')) {
            $this->eventDispatcher->addListener(PageStateChangeCompletedEvent::class, [$this, 'onPageStateChangeCompleted']);
            $this->eventDispatcher->addListener(PageStateResetEvent::class, [$this, 'onPageStateReset']);
        }
    }

    public function onPageStateChangeCompleted(PageStateChangeCompletedEvent $event): void
    {
        $state = $event->getFinalState();
        $this->logger->info(sprintf('PageStateChangeCompletedEvent received: %s', $state->value));
    }

    public function onPageStateReset(PageStateResetEvent $event): void
    {
        $this->logger->info('PageStateResetEvent received');
    }

    /**
     * @route-handler test_page
     */
    public function start(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $requestedStateValue = $queryParams['state'] ?? PageState::INITIAL->value;

        try {
            $requestedState = PageState::from($requestedStateValue);
        } catch (\ValueError $e) {
            $this->logger->warning('Invalid page state requested: ' . $requestedStateValue);
            $requestedState = PageState::ERROR;
        }

        $this->viewModel->requestChange($requestedState);
        return $this->renderState($this->viewModel->getCurrentState());
    }

    /**
     * @route-handler catch_all
     */
    public function handleNotFound(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $this->logger->info('404: Page not found', ['path' => $path]);

        // ✅ Use existing ViewRenderer for consistency
        return $this->renderer->renderContent([
            'state' => PageState::ERROR->value,
            'path' => $path,
            'error' => 'Page Not Found',
            'combined' => false  // Simple 404 template
        ]);
    }

    private function renderState(PageState $state): ResponseInterface
    {
        return $this->renderer->renderContent(['state' => $state->value, 'combined' => true]);
    }
}
