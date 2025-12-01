<?php
declare(strict_types=1);

namespace App\Presentation\Observers;

use App\Presentation\Observers\Interfaces\ObserverInterface;
use Psr\Log\LoggerInterface;

final class SecurityAuditObserver implements ObserverInterface
{
    public function __construct(private LoggerInterface $auditLogger)
    {
    }

    public function update(object $subject, ?string $event = null, mixed $payload = null): void
    {
        if (in_array($event, ['loginFailed', 'userLoggedIn', 'userRegistered', 'userLoggedOut'], true)) {
            $message = sprintf('Security audit: %s event detected', $event);

            $context = [
                'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ];

            if (isset($payload['userId'])) {
                $context['userId'] = $payload['userId'];
            }
            if (isset($payload['usernameOrEmail'])) {
                $context['usernameOrEmail'] = $payload['usernameOrEmail'];
            }

            $this->auditLogger->notice($message, $context);
        }
    }
}
