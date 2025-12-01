<?php
declare(strict_types=1);

namespace App\Application\Session\Services;

use App\Application\Shared\Services\UserContextInterface;
use Psr\Log\LoggerInterface;
use App\Domain\Session\Repositories\SessionRepositoryInterface;

final class SessionGarbageCollector implements SessionGarbageCollectorInterface
{
    public function __construct(
        private readonly SessionRepositoryInterface $sessionRepository,
        private readonly LoggerInterface $logger,
        private readonly UserContextInterface $userContext, // Inject user context
    ) {}

    /**
     * Cleans up expired and revoked sessions per repository logic.
     * Logs count of deleted sessions or errors with user context.
     *
     * @return int Number of sessions deleted
     */
    public function collectGarbage(): int
    {
        $context = [
            'user_id' => $this->userContext->getUserId(),
            'guest_id' => $this->userContext->getGuestId(),
        ];

        try {
            $deletedCount = $this->sessionRepository->deleteExpiredSessions();
            $this->logger->info('Session garbage collection completed', array_merge($context, [
                'deleted_sessions_count' => $deletedCount,
            ]));
            return $deletedCount;
        } catch (\Throwable $e) {
            $this->logger->error('Session garbage collection failed', array_merge($context, [
                'exception' => $e,
            ]));
            throw $e;
        }
    }
}
