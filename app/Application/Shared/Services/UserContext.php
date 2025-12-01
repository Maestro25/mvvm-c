<?php
declare(strict_types=1);

namespace App\Application\Shared\Services;

use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\GuestId;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use Psr\Log\LoggerInterface;

/**
 * UserContext provides current user or guest identity based on session data.
 * Adds detailed logging for debugging and audit purposes.
 */
final class UserContext implements UserContextInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Returns a UserIdInterface representing the current user or guest.
     * Logs attempts and failures explicitly.
     *
     * @return UserId|GuestId|null
     */
    public function getUserId(): ?IdentityInterface
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->logger->warning('Attempted getUserId with inactive session');
            return null;
        }

        try {
            if (!empty($_SESSION['user_id'])) {
                $userId = UserId::fromString($_SESSION['user_id']);
                $this->logger->info('User ID retrieved from session', ['user_id' => (string)$userId]);
                return $userId;
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error parsing user_id from session', ['exception' => $e, 'raw_user_id' => $_SESSION['user_id'] ?? null]);
            return null;
        }

        try {
            if (!empty($_SESSION['guest_id'])) {
                $guestId = GuestId::fromString($_SESSION['guest_id']);
                $this->logger->info('Guest ID fallback retrieved from session', ['guest_id' => (string)$guestId]);
                return $guestId;
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error parsing guest_id from session', ['exception' => $e, 'raw_guest_id' => $_SESSION['guest_id'] ?? null]);
            return null;
        }

        $this->logger->info('No user_id or guest_id found in session');
        return null;
    }

    /**
     * Returns the GuestId VO if guest is present, else null.
     * Logs retrieval status.
     *
     * @return GuestId|null
     */
    public function getGuestId(): ?GuestId
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $this->logger->warning('Attempted getGuestId with inactive session');
            return null;
        }

        try {
            if (!empty($_SESSION['guest_id'])) {
                $guestId = GuestId::fromString($_SESSION['guest_id']);
                $this->logger->info('Guest ID retrieved from session', ['guest_id' => (string)$guestId]);
                return $guestId;
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error parsing guest_id from session', ['exception' => $e, 'raw_guest_id' => $_SESSION['guest_id'] ?? null]);
            return null;
        }

        $this->logger->info('No guest_id found in session');
        return null;
    }
}
