<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Application\Shared\Services\AuditService;
use App\Application\Shared\Services\UserContextInterface;
use App\Application\Session\Translators\SessionArrayToDtoTranslator;
use App\Config\SessionConfig;
use App\Domain\Shared\Exceptions\EntityNotFoundException;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Session\Enums\SessionState;
use App\Domain\Session\Factories\SessionFactoryInterface;
use App\Domain\Session\Repositories\SessionRepositoryInterface;
use App\Domain\Session\Validation\SessionValidatorInterface;
use App\Application\Session\Validation\Exceptions\SessionInvalidException;
use App\Application\Session\Services\SessionCookieManagerInterface;
use App\Application\Session\Services\SessionTokenManagerInterface;
use App\Domain\Session\Events\SessionStartedEvent;
use App\Domain\Session\Events\SessionFailedEvent;
use App\Domain\Session\Events\SessionDestroyedEvent;
use App\Domain\Session\Events\SessionRegeneratedEvent;
use App\Domain\Session\Events\SessionExpiredEvent;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use SessionHandlerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\GuestId;

final class SessionService
{
    private const GUEST_ID_SESSION_KEY = 'guest_id';
    private const USER_ID_SESSION_KEY = 'user_id';

    private ?SessionId $domainSessionId = null;

    public function __construct(
        private readonly SessionHandlerInterface $sessionHandler,
        private readonly SessionTokenManagerInterface $tokenManager,
        private readonly SessionRepositoryInterface $sessionRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly SessionCookieManagerInterface $cookieManager,
        private readonly SessionValidatorInterface $sessionValidator,
        private readonly SessionFactoryInterface $sessionFactory,
        private readonly SessionArrayToDtoTranslator $sessionTranslator,
        private readonly AuditService $auditService,
        private readonly UserContextInterface $userContext,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ServerRequestInterface $request
    ) {
    }

    public function startSession(): void
    {
        $context = $this->additionalLogContext();
        try {
            $this->logger->debug('Starting session initialization', $context);

            $this->logger->debug('Session configuration applied', $context);

            $this->cookieManager->applyCookieParams();
            $this->logger->debug('Session cookie parameters applied', $context);

            session_set_save_handler($this->sessionHandler, true);
            if (!session_start()) {
                throw new \RuntimeException('Failed to start PHP session');
            }

            if (empty($_SESSION['domain_session_id'])) {
                $this->domainSessionId = SessionId::generate();
                $_SESSION['domain_session_id'] = (string) $this->domainSessionId;
            } else {
                $this->domainSessionId = new SessionId($_SESSION['domain_session_id']);
            }

            if (!$this->isUserAuthenticated() && empty($_SESSION[self::GUEST_ID_SESSION_KEY])) {
                $_SESSION[self::GUEST_ID_SESSION_KEY] = (string) GuestId::generate();
                $this->logger->info('Generated new guest ID', ['guest_id' => $_SESSION[self::GUEST_ID_SESSION_KEY]]);
            }

            $userIdString = $_SESSION[self::USER_ID_SESSION_KEY]
                ?? $_SESSION[self::GUEST_ID_SESSION_KEY]
                ?? (string) GuestId::generate();

            if ($this->isUserAuthenticated()) {
                try {
                    $userId = UserId::fromString($userIdString);
                    if (!$this->userRepository->getById($userId)) {
                        $this->logger->warning("User ID in session not found, resetting to guest.", ['user_id' => $userIdString]);
                        unset($_SESSION[self::USER_ID_SESSION_KEY]);
                        $_SESSION[self::GUEST_ID_SESSION_KEY] = (string) GuestId::generate();
                        $userIdString = $_SESSION[self::GUEST_ID_SESSION_KEY];
                    }
                } catch (EntityNotFoundException $e) {
                    $this->logger->warning("Entity not found for user ID in session, resetting to guest.", ['exception' => $e]);
                    unset($_SESSION[self::USER_ID_SESSION_KEY]);
                    $_SESSION[self::GUEST_ID_SESSION_KEY] = (string) GuestId::generate();
                    $userIdString = $_SESSION[self::GUEST_ID_SESSION_KEY];
                }
            }

            if (empty($_SESSION['access_token'])) {
                $sessionWithTokens = $this->tokenManager->createTokensForSession($this->domainSessionId);
                $_SESSION['access_token'] = $sessionWithTokens->getAccessToken()->getToken();
                $_SESSION['refresh_token'] = $sessionWithTokens->getRefreshToken()?->getToken();
                $_SESSION['csrf_token'] = $sessionWithTokens->getCsrfToken()?->getToken();

                $this->logger->debug('Set session tokens in $_SESSION', [
                    'access_token' => $_SESSION['access_token'],
                    'refresh_token' => $_SESSION['refresh_token'],
                    'csrf_token' => $_SESSION['csrf_token'],
                ]);

                $this->logger->info('Generated new session tokens for session', ['session_id' => (string) $this->domainSessionId]);
            }

            $sessionData = $_SESSION;

            // Ensure created_ip is set for DTO
            $sessionData['created_ip'] = $this->request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';

            $sessionData['session_id'] = (string) $this->domainSessionId;
            $sessionData['user_id'] = $userIdString;
            $sessionData['access_token'] = $_SESSION['access_token'];
            $sessionData['refresh_token'] = $_SESSION['refresh_token'] ?? null;
            $sessionData['csrf_token'] = $_SESSION['csrf_token'] ?? null;

            $sessionDto = $this->sessionTranslator->toDto($sessionData);

            // Fix for AuditService argument: pass UserId or null, not GuestId
            $currentUserId = $this->userContext->getUserId();
            if (!($currentUserId instanceof UserId)) {
                $currentUserId = null;
            }
            $auditDto = $this->auditService->createAuditData($this->request, $currentUserId);

            /**
             * @var SessionInterface $session
             */
            $session = $this->sessionFactory->createEntity($this->domainSessionId, $sessionDto, $auditDto);

            // Explicit log token strings before validation
            $this->logger->debug('Tokens before validation', [
                'access_token' => $session->getAccessToken()->getToken(),
                'refresh_token' => $session->getRefreshToken()?->getToken(),
                'csrf_token' => $session->getCsrfToken()?->getToken(),
            ]);

            if (!$this->sessionValidator->validate($session)) {
                $errors = $this->sessionValidator->getValidationErrors();
                $this->logger->warning('Session validation errors', ['errors' => $errors]);
                throw new SessionInvalidException('Session is invalid: ' . implode(', ', $errors));
            }

            $this->eventDispatcher->dispatch(new SessionStartedEvent(SessionState::STARTED));
            $this->logger->info('Session started', ['session_id' => (string) $this->domainSessionId]);

            $this->regenerateSessionIdPeriodically();

            $this->initializeIds();

            $this->cookieManager->renewSessionCookie();
            $this->logger->debug('Session cookie renewed after start', $context);
        } catch (\Throwable $e) {
            $this->eventDispatcher->dispatch(new SessionFailedEvent(SessionState::FAILED));
            $this->logger->error('Failed to start session', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    private function initializeIds(): void
    {
        if (!$this->isUserAuthenticated()) {
            if (empty($_SESSION[self::GUEST_ID_SESSION_KEY])) {
                $guestId = GuestId::generate();
                $_SESSION[self::GUEST_ID_SESSION_KEY] = (string) $guestId;
                $this->logger->info('Guest ID initialized', ['guest_id' => $_SESSION[self::GUEST_ID_SESSION_KEY]]);
            }
            unset($_SESSION[self::USER_ID_SESSION_KEY]);
        }
    }

    public function isUserAuthenticated(): bool
    {
        return !empty($_SESSION[self::USER_ID_SESSION_KEY]);
    }

    public function setUserId(UserId $userId): void
    {
        $_SESSION[self::USER_ID_SESSION_KEY] = (string) $userId;
        unset($_SESSION[self::GUEST_ID_SESSION_KEY]);

        $this->logger->info('User ID set in session', ['user_id' => (string) $userId]);
    }

    public function clearUserId(): void
    {
        unset($_SESSION[self::USER_ID_SESSION_KEY]);
        $this->initializeIds();

        $this->logger->info('User logged out; guest ID reinitialized if needed', [
            'guest_id' => $_SESSION[self::GUEST_ID_SESSION_KEY] ?? null,
        ]);
    }

    private function regenerateSessionIdPeriodically(): void
    {
        $now = time();
        $lastRegen = $_SESSION[SessionConfig::SESSION_LAST_REGENERATION_KEY] ?? 0;
        $context = $this->additionalLogContext() + ['last_regeneration' => $lastRegen, 'current_time' => $now];

        if (($now - $lastRegen) > SessionConfig::SESSION_REGENERATION_INTERVAL) {
            $this->logger->debug('Session ID regeneration triggered', $context);
            if (!session_regenerate_id(true)) {
                $this->logger->warning('Session ID regeneration failed', $context);
                return;
            }
            $_SESSION[SessionConfig::SESSION_LAST_REGENERATION_KEY] = $now;
            $this->cookieManager->renewSessionCookie();
            $this->eventDispatcher->dispatch(new SessionRegeneratedEvent(SessionState::REGENERATED));
            $this->logger->info('Session ID regenerated', array_merge($context, ['new_session_id' => session_id()]));
        } else {
            $this->logger->debug('Session ID regeneration skipped; interval not reached', $context);
        }
    }

    public function validateAccessToken(string $token): ?SessionInterface
    {
        $context = $this->additionalLogContext() + ['token' => $token];
        try {
            $this->logger->debug('Validating access token', $context);
            $session = $this->tokenManager->validateAccessToken($token);

            if ($session === null) {
                $this->logger->notice('Access token validation failed: session not found or inactive', $context);
                return null;
            }

            if (!$this->sessionValidator->validate($session)) {
                $errors = $this->sessionValidator->getValidationErrors();
                $this->logger->warning('Session validation errors', array_merge($context, ['errors' => $errors]));
                throw new SessionInvalidException('Invalid session: ' . implode(', ', $errors));
            }

            $this->logger->info('Access token validated successfully', array_merge($context, ['session_id' => (string) $session->getId()]));
            return $session;
        } catch (\Throwable $e) {
            $this->logger->error('Access token validation exception', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    public function destroySession(): void
    {
        $context = $this->additionalLogContext() + ['session_id' => session_id()];
        try {
            $this->logger->debug('Destroying session', $context);
            $_SESSION = [];

            if (session_id() !== '') {
                session_destroy();
                $this->eventDispatcher->dispatch(new SessionDestroyedEvent(SessionState::DESTROYED));
                $this->logger->info('Session destroyed', $context);
            }

            $this->cookieManager->clearSessionCookie();
            $this->logger->debug('Session cookie cleared after destroy', $context);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to destroy session', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    public function renewSessionTokens(SessionId $sessionId): SessionInterface
    {
        $context = $this->additionalLogContext() + ['session_id' => (string) $sessionId];
        try {
            $this->logger->debug('Renewing session tokens', $context);
            $session = $this->tokenManager->createTokensForSession($sessionId);

            if (!$this->sessionValidator->validate($session)) {
                $errors = $this->sessionValidator->getValidationErrors();
                $this->logger->warning('Session token renewal failed validation', array_merge($context, ['errors' => $errors]));
                throw new SessionInvalidException('Invalid session: ' . implode(', ', $errors));
            }

            $this->logger->info('Session tokens renewed successfully', $context);
            return $session;
        } catch (\Throwable $e) {
            $this->logger->error('Exception during session token renewal', array_merge($context, ['exception' => $e]));
            throw $e;
        }
    }

    public function updateSessionMeta(SessionId $sessionId, array $meta): bool
    {
        $context = $this->additionalLogContext() + ['session_id' => (string) $sessionId, 'meta' => $meta];
        $result = $this->sessionRepository->updateMetadata($sessionId, $meta);

        if ($result) {
            $this->logger->info('Session metadata updated', $context);
        } else {
            $this->logger->warning('Failed to update session metadata', $context);
        }

        return $result;
    }

    private function additionalLogContext(): array
    {
        return [
            'user_id' => $_SESSION[self::USER_ID_SESSION_KEY] ?? null,
            'guest_id' => $_SESSION[self::GUEST_ID_SESSION_KEY] ?? null,
            'session_id' => session_id() ?: null,
        ];
    }
}
