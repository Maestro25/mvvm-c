<?php
declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\ValueObjects\UserId;

final class UserLoggedOutEvent extends DomainEvent implements DomainEventInterface
{
    public function __construct(
        private readonly UserId $userId,
        private readonly \DateTimeImmutable $loggedOutAt = new \DateTimeImmutable()
    ) {
        parent::__construct();
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getLoggedOutAt(): \DateTimeImmutable
    {
        return $this->loggedOutAt;
    }
}
