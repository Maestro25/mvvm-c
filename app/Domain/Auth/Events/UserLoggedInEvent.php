<?php
declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Events\Interfaces\DomainEventInterface;
use App\Domain\ValueObjects\UserId;

final class UserLoggedInEvent extends DomainEvent implements DomainEventInterface
{
    public function __construct(
        private readonly UserId $userId,
        private readonly \DateTimeImmutable $loggedInAt = new \DateTimeImmutable()
    ) {
        parent::__construct();
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getLoggedInAt(): \DateTimeImmutable
    {
        return $this->loggedInAt;
    }
}
