<?php
declare(strict_types=1);

namespace App\Presentation\Coordinators;

use Psr\Log\LoggerInterface;
use App\Presentation\ViewModels\AuthViewModel;
use App\Presentation\Controllers\AuthViewController;
use App\Application\Services\Authentication\Interfaces\AuthServiceInterface;
use App\Application\Services\Session\Interfaces\SessionServiceInterface;
use App\Infrastructure\Core\Routing\UrlGenerator;
use App\Presentation\ViewModels\Enums\PageState;
use App\Infrastructure\Core\Routing\RouteNames;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Throwable;

/**
 * AuthCoordinator handles authentication-related flows,
 * managing login, registration, logout, and related UI navigation.
 */
final class AuthCoordinator extends Coordinator
{
    private AuthViewModel $viewModel;
    private AuthViewController $controller;
    private AuthServiceInterface $authService;
    private SessionServiceInterface $sessionService;
    protected LoggerInterface $logger;
    private UrlGenerator $urlGenerator;

    private string $route = RouteNames::LOGIN; // Default route

    private bool $isHandlingEvent = false;

    private ?string $lastNavigatedState = null;

    public function __construct(
        AuthViewModel $viewModel,
        AuthViewController $controller,
        AuthServiceInterface $authService,
        SessionServiceInterface $sessionService,
        LoggerInterface $logger,
        UrlGenerator $urlGenerator
    ) {
        parent::__construct($logger);

        $this->viewModel = $viewModel;
        $this->controller = $controller;
        $this->authService = $authService;
        $this->sessionService = $sessionService;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;

        $this->attachViewModelObserver(
            $this->viewModel,
            fn(string $event, array $context = []) => $this->handleViewModelEvent($event, $context)
        );
    }

    /**
     * Set the route internally for the coordinator (e.g. login or register)
     */
    public function setRoute(string $route): void
    {
        $this->logger->info("AuthCoordinator setRoute called with route: {$route}");
        $this->route = $route;
        // Reset last navigated state on explicit route set to avoid stale state checks
        $this->lastNavigatedState = null;
    }

    /**
     * Starts the Auth flow by rendering the initial authentication page
     * based on internally set route.
     *
     * @return ResponseInterface
     */
    public function start(): ResponseInterface
    {
        try {
            $this->onStart();
            $this->logger->info("AuthCoordinator start called with route {$this->route}");

            if (!in_array($this->route, [RouteNames::LOGIN, RouteNames::REGISTER,], true)) {
                $this->logger->warning("Invalid route '{$this->route}' detected; defaulting to login");
                $this->route = RouteNames::LOGIN;
            }

            return match ($this->route) {
                RouteNames::REGISTER => $this->startRegister(),
                RouteNames::LOGIN => $this->startLogin(),
               
                default => $this->handleUnknownRoute($this->route),
            };
        } catch (Throwable $e) {
            $this->logError('AuthCoordinator start failure: ' . $e->getMessage(), $e);
            return $this->renderWithError(PageState::LOGIN, 'Unexpected error occurred.');
        }
    }

    /**
     * Start registration page flow.
     */
    private function startRegister(): ResponseInterface
    {
        $this->viewModel->setPageState(PageState::REGISTER);
        $this->lastNavigatedState = PageState::REGISTER->value;
        return $this->renderRegister();
    }

    /**
     * Start login page flow.
     */
    private function startLogin(): ResponseInterface
    {
        $this->viewModel->setPageState(PageState::LOGIN);
        $this->lastNavigatedState = PageState::LOGIN->value;
        return $this->renderLogin();
    }

    /**
     * Handle unknown routes gracefully with 404.
     */
    private function handleUnknownRoute(string $route): ResponseInterface
    {
        $this->logger->error("Unknown route passed to AuthCoordinator: {$route}");
        return new HtmlResponse('Page not found', 404);
    }

    /**
     * Processes user login with given credentials.
     *
     * @param array<string, mixed> $credentials Associative keys: usernameOrEmail, password, rememberMe
     * @return ResponseInterface
     */
    public function login(array $credentials): ResponseInterface
    {
        $loginVM = $this->viewModel->getLoginViewModel();

        // Sanitize and validate input
        $usernameOrEmail = filter_var($credentials['usernameOrEmail'] ?? '', FILTER_SANITIZE_STRING);
        $password = $credentials['password'] ?? '';
        $rememberMe = filter_var($credentials['rememberMe'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $loginVM->setUsernameOrEmail($usernameOrEmail);
        $loginVM->setPassword($password);
        $loginVM->setRememberMe($rememberMe);

        $this->viewModel->setPageState(PageState::VALIDATING);

        if (!$loginVM->validate()) {
            $this->viewModel->addFlashMessage('error', 'Validation failed for login.');
            $this->viewModel->setPageState(PageState::VALIDATED_FAILURE);
            return $this->renderLogin();
        }

        try {
            $this->authService->login(
                new \App\Application\UseCases\User\Login\LoginUserRequest(
                    $loginVM->getUsernameOrEmail(),
                    $loginVM->getPassword()
                )
            );

            $this->sessionService->enforceSecurity();
            $this->sessionService->refreshLastActivity();

            $this->viewModel->setPageState(PageState::AUTHENTICATED);
            $this->viewModel->notifyObservers('loginSuccessful');

            $redirectUrl = $this->urlGenerator->generate(RouteNames::DASHBOARD);
            return new RedirectResponse($redirectUrl);
        } catch (Throwable $e) {
            $this->logError('Login failed: ' . $e->getMessage(), $e);
            $this->viewModel->addFlashMessage('error', 'Invalid credentials provided.');
            $this->viewModel->setPageState(PageState::VALIDATED_FAILURE);
            $this->viewModel->notifyObservers('validationFailed');
            return $this->renderLogin();
        }
    }

    /**
     * Processes new user registration.
     *
     * @param array<string, mixed> $userData Keys include username, email, password, confirmPassword, acceptedTerms
     * @return ResponseInterface
     */
    public function register(array $userData): ResponseInterface
    {
        $registerVM = $this->viewModel->getRegisterViewModel();

        // Sanitize inputs
        $username = filter_var($userData['username'] ?? '', FILTER_SANITIZE_STRING);
        $email = filter_var($userData['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
        $password = $userData['password'] ?? '';
        $confirmPassword = $userData['confirmPassword'] ?? '';
        $acceptedTerms = filter_var($userData['acceptedTerms'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $registerVM->setUsername($username);
        $registerVM->setEmail($email);
        $registerVM->setPassword($password);
        $registerVM->setConfirmPassword($confirmPassword);
        $registerVM->setAcceptedTerms($acceptedTerms);

        $this->viewModel->setPageState(PageState::VALIDATING);

        if (!$registerVM->validate()) {
            $this->viewModel->addFlashMessage('error', 'Validation failed for registration.');
            $this->viewModel->setPageState(PageState::VALIDATED_FAILURE);
            return $this->renderRegister();
        }

        try {
            $this->authService->registerAndLogin(
                new \App\Application\UseCases\User\Register\RegisterUserRequest(
                    $registerVM->getUsername(),
                    $registerVM->getEmail(),
                    $registerVM->getPassword()
                )
            );

            $this->sessionService->enforceSecurity();
            $this->sessionService->refreshLastActivity();

            $this->viewModel->setPageState(PageState::AUTHENTICATED);
            $this->viewModel->notifyObservers('registrationSuccessful');

            $redirectUrl = $this->urlGenerator->generate(RouteNames::DASHBOARD);
            return new RedirectResponse($redirectUrl);
        } catch (Throwable $e) {
            $this->logError('Registration failed: ' . $e->getMessage(), $e);
            $this->viewModel->addFlashMessage('error', 'Registration failed. Please check details and try again.');
            $this->viewModel->setPageState(PageState::VALIDATED_FAILURE);
            $this->viewModel->notifyObservers('validationFailed');

            return $this->renderRegister();
        }
    }

    /**
     * Handles logout and session termination.
     *
     * @return ResponseInterface
     */
    public function logout(): ResponseInterface
    {
        try {
            $this->authService->logout();
            $this->sessionService->terminateSession();

            $this->viewModel->setPageState(PageState::LOGOUT);
            $this->viewModel->notifyObservers('logoutSuccessful');

            $redirectUrl = $this->urlGenerator->generate(RouteNames::LOGIN);
            return new RedirectResponse($redirectUrl);
        } catch (Throwable $e) {
            $this->logError('Logout failed: ' . $e->getMessage(), $e);
            return $this->renderWithError(PageState::LOGIN, 'Logout failed, please try again.');
        }
    }

    /**
     * Routes navigation to appropriate page state view.
     *
     * @param string $state One of PageState enum values
     * @return ResponseInterface
     */
    public function navigate(string $state): ResponseInterface
    {
        if ($this->lastNavigatedState === $state) {
            $this->logger->info("Avoiding redundant navigate call for state: {$state}");
            // Returning cached render matching last state to prevent event loop
            return match ($state) {
                PageState::LOGIN->value => $this->renderLogin(),
                PageState::REGISTER->value => $this->renderRegister(),
                PageState::LOGOUT->value => $this->renderLogin(),
                default => $this->renderLogin()
            };
        }

        $this->lastNavigatedState = $state;

        // $this->logger->info("AuthCoordinator navigating to page state: {$state}");

        try {
            $pageState = PageState::from($state);
        } catch (\ValueError $e) {
            $this->logger->error("Invalid page state for navigation: {$state}");
            return $this->renderLoginWithError("Unknown page state requested.");
        }

        $this->viewModel->setPageState($pageState);

        return match ($pageState) {
            PageState::LOGIN => $this->renderLogin(),
            PageState::REGISTER => $this->renderRegister(),
            PageState::VALIDATING => $this->renderLoading(),
            PageState::PROCESSING => $this->renderLoading(),
            PageState::VALIDATED_SUCCESS => $this->renderSuccessPage(),
            PageState::VALIDATED_FAILURE => $this->renderErrorPage(),
            PageState::LOGOUT => $this->renderLogin(),
            PageState::SESSION_EXPIRED => $this->renderSessionExpired(),
            default => $this->renderLoginWithError("Unknown page state: {$state}"),
        };
    }


    private function renderLogin(): ResponseInterface
    {
        $this->logger->info('Rendering login page');
        $data = [
            'flashMessages' => $this->viewModel->getFlashMessages(),
            'notifications' => $this->viewModel->getNotifications(),
            'rememberMe' => $this->viewModel->getRememberMe(),
            'loginData' => $this->viewModel->getLoginViewModel()->toArray(),
            'resetPasswordUrl' => $this->urlGenerator->generate(RouteNames::RESET_PASSWORD),
            'registerUrl' => $this->urlGenerator->generate(RouteNames::REGISTER),
        ];

        return $this->controller->renderLoginPage($data);
    }

    private function renderRegister(): ResponseInterface
    {
        $this->logger->info('Rendering register page');
        $data = [
            'flashMessages' => $this->viewModel->getFlashMessages(),
            'notifications' => $this->viewModel->getNotifications(),
            'registerData' => $this->viewModel->getRegisterViewModel()->toArray(),
            'loginUrl' => $this->urlGenerator->generate(RouteNames::LOGIN),
        ];

        $response = $this->controller->renderRegisterPage($data);

        $bodyContent = (string) $response->getBody();
        $this->logger->info('renderRegister: Response body length: ' . strlen($bodyContent));

        return $response;
    }


    private function renderLoading(): ResponseInterface
    {
        $this->logger->info('Rendering loading page');
        // Return a dedicated loading page template to avoid loops
        return new HtmlResponse('<html><body><h2>Loading...</h2></body></html>', 200);
    }

    private function renderSuccessPage(): ResponseInterface
    {
        $this->logger->info('Rendering success page');
        // Return a simple message or redirect to dashboard
        return new RedirectResponse($this->urlGenerator->generate(RouteNames::DASHBOARD));
    }

    private function renderErrorPage(): ResponseInterface
    {
        $this->logger->info('Rendering error page');
        return $this->renderLogin();
    }

    private function renderSessionExpired(): ResponseInterface
    {
        $this->logger->info('Rendering session expired page');
        $this->viewModel->addFlashMessage('error', 'Your session has expired. Please login again.');
        $this->viewModel->setPageState(PageState::LOGIN);
        // Reset last state for navigation
        $this->lastNavigatedState = null;
        return $this->renderLogin();
    }

    private function renderLoginWithError(string $message): ResponseInterface
    {
        $this->logger->info("Rendering login page with error message: {$message}");
        $this->viewModel->addFlashMessage('error', $message);
        return $this->renderLogin();
    }

    private function renderWithError(PageState $page, string $message): ResponseInterface
    {
        $this->logger->info("Rendering with error on page state {$page->value}: {$message}");
        $this->viewModel->addFlashMessage('error', $message);
        $this->viewModel->setPageState($page);
        // Reset last state
        $this->lastNavigatedState = null;
        return $this->navigate($page->value);
    }

    /**
     * Handles ViewModel events.
     *
     * @param string $event
     * @param array<string, mixed> $context
     * @return ResponseInterface|null
     */
     private function handleViewModelEvent(string $event, array $context = []): ?ResponseInterface
    {
        if ($this->isHandlingEvent) {
            $this->logger->warning("Re-entrant event handling avoided for event: {$event}");
            return null;
        }
        $this->isHandlingEvent = true;

        try {
            return match ($event) {
                'pageStateChanged' => 
                    isset($context['state']) 
                    ? (
                        $context['state'] !== $this->lastNavigatedState
                        ? $this->navigate($context['state'])
                        : null
                      )
                    : null,
                'loginSuccessful' => null,
                'registrationSuccessful' => null,
                'logoutSuccessful' => $this->renderLogin(),
                'validationFailed' => $this->renderErrorPage(),
                default => $this->logger->debug("Unhandled event in AuthCoordinator: {$event}") ?: null,
            };
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
