<?php
declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Events\Interfaces\DomainEventInterface;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\UserId;

final class UserRegisteredEvent extends DomainEvent implements DomainEventInterface
{
    public function __construct(
        private readonly UserId $userId,
        private readonly Email $email,
        private readonly \DateTimeImmutable $registeredAt = new \DateTimeImmutable()
    ) {
        parent::__construct();
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getRegisteredAt(): \DateTimeImmutable
    {
        return $this->registeredAt;
    }
}
