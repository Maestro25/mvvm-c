<?php
declare(strict_types=1);

namespace App\Presentation\Observers;

use App\Presentation\Observers\Interfaces\ObserverInterface;
use Psr\Log\LoggerInterface;

final class AuthLoggingObserver implements ObserverInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function update(object $subject, ?string $event = null, mixed $payload = null): void
    {
        switch ($event) {
            case 'userRegistered':
                $this->logger->info('User registration completed', [
                    'userId' => $payload['userId'] ?? 'unknown',
                    'email' => $payload['email'] ?? 'unknown',
                ]);
                break;

            case 'userLoggedIn':
                $this->logger->info('User logged in', [
                    'userId' => $payload['userId'] ?? 'unknown',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'n/a',
                ]);
                break;

            case 'userLoggedOut':
                $this->logger->info('User logged out', [
                    'userId' => $payload['userId'] ?? 'unknown',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'n/a',
                ]);
                break;

            case 'loginFailed':
                $this->logger->warning('Login attempt failed', [
                    'usernameOrEmail' => $payload['usernameOrEmail'] ?? 'unknown',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'n/a',
                    'reason' => $payload['reason'] ?? 'unknown',
                ]);
                break;

            default:
                $this->logger->debug(
                    sprintf('Auth event observed: %s', $event ?? 'unknown'),
                    ['payload' => $payload]
                );
                break;
        }
    }
}
