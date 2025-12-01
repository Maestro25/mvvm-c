<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use App\Domain\Shared\Exceptions\InvalidUuidException;
use App\Domain\Shared\Traits\HasUuidIdentity;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Domain\Shared\ValueObjects\Uuid;

final class ProfileId implements IdentityInterface
{
    use HasUuidIdentity;

    public function __construct(string|Uuid $uuid)
    {
        $this->initUuid($uuid);
    }
}
