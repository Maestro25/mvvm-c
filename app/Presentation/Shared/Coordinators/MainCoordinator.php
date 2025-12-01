<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators;

use Psr\Log\LoggerInterface;
use App\Presentation\ViewModels\MainViewModel;
use App\Presentation\Controllers\MainViewController;
use App\Infrastructure\Core\Routing\UrlGenerator;
use App\Infrastructure\Core\Routing\RouteNames;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Throwable;
use App\Application\Services\Authentication\Interfaces\AuthServiceInterface;

/**
 * MainCoordinator manages the main app flow,
 * handles routing, view model, rendering with authentication partials.
 */
final class MainCoordinator extends Coordinator
{
    private MainViewModel $viewModel;
    private MainViewController $controller;
    protected LoggerInterface $logger;
    private UrlGenerator $urlGenerator;
    private AuthServiceInterface $authService;
    private ?string $requestedRoute = null;

    private bool $isHandlingEvent = false;

    private const SAFE_REDIRECTS = [
        RouteNames::DASHBOARD => RouteNames::DASHBOARD,
        RouteNames::PROFILE => RouteNames::PROFILE,
        RouteNames::SETTINGS => RouteNames::SETTINGS,
    ];

    private const VIEW_PARTIALS = [
        RouteNames::DASHBOARD => 'main/dashboard',
        RouteNames::PROFILE => 'main/profile',
        RouteNames::SETTINGS => 'main/settings',
    ];

    public function __construct(
        LoggerInterface $logger,
        UrlGenerator $urlGenerator,
        AuthServiceInterface $authService,
    ) {
        parent::__construct($logger);

        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->authService = $authService;

        // Initialize ViewModel once to ensure reuse and stable state
        $this->viewModel = new MainViewModel([], null, $logger);
        $this->controller = new MainViewController($logger, $this->urlGenerator);

        $this->attachViewModelObserver(
            $this->viewModel,
            fn(string $event, array $context = []) => $this->onViewModelEvent($event, $context)
        );
    }

    /**
     * Sets the internal route.
     */
    public function setRoute(string $route): void
    {
        $this->requestedRoute = $route;
        $this->logger->info("MainCoordinator route set to: {$route}");
    }

    /**
     * Start the main flow; render main shell page with auth partial embedded.
     */
    public function start(): ResponseInterface
    {
        try {
            $this->onStart();

            $isUserAuthenticated = $this->authService->isAuthenticated();
            $currentUserName = $isUserAuthenticated ? $this->authService->getCurrentUserName() : null;

            // Compose main page content with authentication info as part of data
            $data = [
                'pageTitle' => 'Main Page',
                'currentUserName' => $currentUserName,
                'isUserAuthenticated' => $isUserAuthenticated,
                'pageState' => $this->viewModel->getPageState()->value,
                'flashMessages' => $this->viewModel->getFlashMessages(),
                'notifications' => $this->viewModel->getNotifications(),
                'contentView' => self::VIEW_PARTIALS[$this->requestedRoute] ?? 'main/content',
                // Add a partial for login UI when user is not authenticated
                'authPartial' => !$isUserAuthenticated ? 'auth/login-partial' : '',
            ];
            $this->logger->info('Rendering main shell page with content: ' . ($this->requestedRoute ?? 'none'));
            return $this->controller->renderShell($data);
        } catch (Throwable $e) {
            $this->logError('MainCoordinator start failure: ' . $e->getMessage(), $e);
            return $this->controller->renderErrorPage($e->getMessage());
        }
    }

    /**
     * Navigate internal routes and optionally redirect.
     */
    public function navigate(string $destination, bool $isRedirect = false): ResponseInterface
    {
        // $this->logger->info("MainCoordinator navigating to route: {$destination}");

        if ($isRedirect) {
            if (isset(self::SAFE_REDIRECTS[$destination])) {
                $uri = $this->urlGenerator->generate(self::SAFE_REDIRECTS[$destination]);
                return new RedirectResponse($uri);
            }
            $this->logger->warning("Unsafe redirect attempt: {$destination}");
            $uri = $this->urlGenerator->generate(RouteNames::DASHBOARD);
            return new RedirectResponse($uri);
        }

        if (isset(self::VIEW_PARTIALS[$destination])) {
            $this->setRoute($destination);
            $this->logger->info("Rendering partial for route: {$destination}");
            return $this->start();
        }

        // Redirect to login or register if those routes requested
        if (in_array($destination, [RouteNames::LOGIN, RouteNames::REGISTER], true)) {
            $uri = $this->urlGenerator->generate($destination);
            return new RedirectResponse($uri);
        }

        return $this->controller->renderErrorPage("Unknown destination: {$destination}");
    }

    /**
     * React to ViewModel events for UI flow control.
     */
    private function onViewModelEvent(string $event, array $context = []): ?ResponseInterface
    {
        if ($this->isHandlingEvent) {
            $this->logger->warning("Re-entrant event handling avoided for event: {$event}");
            return null;
        }
        $this->isHandlingEvent = true;

        try {
            switch ($event) {
                case 'pageStateChanged':
                    $this->logger->info("Page state changed: {$context['state']}");
                    return null;

                case 'userAuthenticationChanged':
                    if (!empty($context['authenticated'])) {
                        return $this->navigate(RouteNames::DASHBOARD, true);
                    }
                    return $this->navigate(RouteNames::LOGIN, true);

                default:
                    $this->logger->debug("Unhandled ViewModel event: {$event}");
                    return null;
            }
        } finally {
            $this->isHandlingEvent = false;
        }
    }

    /**
     * Stops coordinator and clears observers.
     */
    public function stop(): void
    {
        $this->onStop();
    }

    /**
     * Cleanup on stop lifecycle event.
     */
    protected function onStop(): void
    {
        $this->clearViewModelObservers();
        parent::onStop();
    }
}
