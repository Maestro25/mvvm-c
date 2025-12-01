<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Presentation\Coordinators\MainCoordinator;
use App\Presentation\Coordinators\AuthCoordinator;
use App\Presentation\ViewModels\AuthViewModel;
use App\Presentation\ViewModels\MainViewModel;
use App\Presentation\Controllers\AuthViewController;
use App\Presentation\Controllers\MainViewController;
use App\Application\Services\Authentication\Interfaces\AuthServiceInterface;
use App\Application\Services\Session\Interfaces\SessionServiceInterface;
use App\Infrastructure\Core\Routing\UrlGenerator;
use App\Infrastructure\Core\Routing\RouteNames;
use App\Presentation\ViewModels\Enums\PageState;
use Nyholm\Psr7\Factory\Psr17Factory;
use Laminas\Diactoros\Response\RedirectResponse;
use Throwable;

/**
 * AppCoordinator is the root coordinator managing top-level flows (auth and main).
 */
final class AppCoordinator extends Coordinator
{
    private ?AuthCoordinator $authCoordinator = null;
    private ?MainCoordinator $mainCoordinator = null;
    private Psr17Factory $responseFactory;

    private AuthViewModel $authViewModel;
    private MainViewModel $mainViewModel;

    private AuthViewController $authController;
    private MainViewController $mainController;

    private AuthServiceInterface $authService;
    private SessionServiceInterface $sessionService;

    private UrlGenerator $urlGenerator;
    private ?string $requestedRoute = null;

    private const ROUTE_TO_PAGE_STATE = [
        RouteNames::LOGIN => PageState::LOGIN->value,
        RouteNames::REGISTER => PageState::REGISTER->value,
    ];

    public function __construct(
        LoggerInterface $logger,
        AuthViewModel $authViewModel,
        MainViewModel $mainViewModel,
        AuthViewController $authController,
        MainViewController $mainController,
        AuthServiceInterface $authService,
        SessionServiceInterface $sessionService,
        UrlGenerator $urlGenerator,
    ) {
        parent::__construct($logger);

        $this->responseFactory = new Psr17Factory();

        $this->authViewModel = $authViewModel;
        $this->mainViewModel = $mainViewModel;

        $this->authController = $authController;
        $this->mainController = $mainController;

        $this->authService = $authService;
        $this->sessionService = $sessionService;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Set the requested route.
     */
    public function setRequestedRoute(string $route): void
    {
        $this->requestedRoute = $route;
    }

    /**
     * Start root application flow, delegate to auth or main coordinator.
     */
    public function start(): ResponseInterface
    {
        try {
            $this->onStart();

            $this->logger->info("AppCoordinator started with route: " . ($this->requestedRoute ?? 'none'));

            $route = $this->requestedRoute ?? RouteNames::MAIN;

            $authRoutes = [RouteNames::LOGIN, RouteNames::REGISTER, RouteNames::RESET_PASSWORD];
            $mainRoutes = [RouteNames::MAIN, RouteNames::DASHBOARD, RouteNames::PROFILE, RouteNames::SETTINGS];

            $isAuthenticated = $this->authService->isAuthenticated();

            if ($isAuthenticated) {
                // Redirect authenticated users away from login/register pages
                if (in_array($route, $authRoutes, true)) {
                    $redirectUrl = $this->urlGenerator->generate(RouteNames::DASHBOARD);
                    return new RedirectResponse($redirectUrl);
                }

                // Proceed to main routes
                if (in_array($route, $mainRoutes, true)) {
                    return $this->startMainCoordinatorWithRoute($route);
                }
            } else {
                // User is not authenticated
                // If trying to access a protected main route, redirect to login
                if (in_array($route, $mainRoutes, true)) {
                    return $this->startAuthCoordinatorWithRoute(RouteNames::LOGIN);
                }

                // If requesting an auth route (login, register...), allow
                if (in_array($route, $authRoutes, true)) {
                    return $this->startAuthCoordinatorWithRoute($route);
                }
            }

            // Fallback unknown route
            return $this->createErrorResponse("Unknown route: {$route}");
        } catch (Throwable $e) {
            $this->logError('AppCoordinator start failure: ' . $e->getMessage(), $e);
            return $this->createErrorResponse('Internal Server Error');
        }
    }


    /**
     * Navigate within the app by logical destination.
     */
    public function navigate(string $destination): ResponseInterface
    {
        // $this->logger->info("AppCoordinator navigating to route: {$destination}");

        // Log current page states for both coordinators if they exist
        if ($this->authCoordinator !== null) {
            $authPageState = $this->authViewModel->getPageState()->value ?? 'undefined';
            $this->logger->info("AuthCoordinator current page state: {$authPageState}");
        }
        if ($this->mainCoordinator !== null) {
            $mainPageState = $this->mainViewModel->getPageState()->value ?? 'undefined';
            $this->logger->info("MainCoordinator current page state: {$mainPageState}");
        }

        // Determine and start appropriate coordinator based on destination
        if (in_array($destination, [RouteNames::LOGIN, RouteNames::REGISTER], true)) {
            return $this->startAuthCoordinatorWithRoute($destination);
        }

        if (in_array($destination, [RouteNames::DASHBOARD, RouteNames::MAIN, RouteNames::PROFILE, RouteNames::SETTINGS], true)) {
            return $this->startMainCoordinatorWithRoute($destination);
        }

        $this->logger->warning("Navigation to unknown destination requested: {$destination}");
        return $this->createErrorResponse("Unknown destination: {$destination}");
    }

    /**
     * Create generic error response.
     */
    private function createErrorResponse(string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse(500);
        $response->getBody()->write($message);
        return $response;
    }

    /**
     * Start MainCoordinator, initializing only if needed.
     */
    private function startMainCoordinatorWithRoute(string $route): ResponseInterface
    {
        if ($this->mainCoordinator === null) {
            $this->mainCoordinator = new MainCoordinator($this->logger, $this->urlGenerator, $this->authService);
            $this->addChild($this->mainCoordinator);
        }
        $this->mainCoordinator->setRoute($route);
        return $this->mainCoordinator->start();
    }

    /**
     * Start AuthCoordinator, initializing only if needed.
     */
    private function startAuthCoordinatorWithRoute(string $route): ResponseInterface
    {
        if ($this->authCoordinator === null) {
            $this->authCoordinator = new AuthCoordinator(
                $this->authViewModel,
                $this->authController,
                $this->authService,
                $this->sessionService,
                $this->logger,
                $this->urlGenerator,
            );
            $this->addChild($this->authCoordinator);
        }

        // Set the requested route explicitly to match requested auth page
        $this->authCoordinator->setRoute($route);

        // Start AuthCoordinator flow based on this route
        return $this->authCoordinator->start();
    }

    /**
     * Stops coordinator and clears children and references
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
        if ($this->authCoordinator !== null) {
            $this->authCoordinator->stop();
            $this->removeChild($this->authCoordinator);
            $this->authCoordinator = null;
        }

        if ($this->mainCoordinator !== null) {
            $this->mainCoordinator->stop();
            $this->removeChild($this->mainCoordinator);
            $this->mainCoordinator = null;
        }

        parent::onStop();
    }
}
