<?php
declare(strict_types=1);

namespace App\Application\Shared\Services;

use App\Domain\Shared\ValueObjects\GuestId;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\ValueObjects\UserId;

interface UserContextInterface
{
    public function getUserId(): ?IdentityInterface;
    public function getGuestId(): ?GuestId;
}
