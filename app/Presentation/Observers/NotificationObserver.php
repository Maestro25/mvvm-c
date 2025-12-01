<?php
declare(strict_types=1);

namespace App\Presentation\Observers;

use App\Application\Services\Notification\Interfaces\NotificationServiceInterface;
use App\Presentation\Observers\Interfaces\ObserverInterface;

final class NotificationObserver implements ObserverInterface
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService
    ) {
    }

    public function update(object $subject, ?string $event = null, mixed $payload = null): void
    {
        switch ($event) {
            case 'userRegistered':
                $email = $payload['email'] ?? null;
                if ($email !== null) {
                    $this->notificationService->sendWelcomeEmail($email);
                }
                break;

            // Optional cases for other notifications can be added here
        }
    }
}
