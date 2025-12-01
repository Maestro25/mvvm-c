<?php
declare(strict_types=1);

namespace App\Domain\Shared\Factories;

use App\Domain\Shared\ValueObjects\Uuid;
use App\Domain\Shared\ValueObjects\GuestId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\PasswordHistoryId;
use App\Domain\Shared\ValueObjects\PermissionId;
use App\Domain\Shared\ValueObjects\ProfileId;
use App\Domain\Shared\ValueObjects\ResetTokenId;
use App\Domain\Shared\ValueObjects\RoleId;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\VerificationTokenId;

interface IdentityFactoryInterface
{
    public function createUserId(string|Uuid|null $uuid = null): UserId;

    public function createRoleId(string|Uuid|null $uuid = null): RoleId;

    public function createPermissionId(string|Uuid|null $uuid = null): PermissionId;

    public function createGuestId(string|Uuid|null $uuid = null): GuestId;

    public function createPasswordHistoryId(string|Uuid|null $uuid = null): PasswordHistoryId;

    public function createProfileId(string|Uuid|null $uuid = null): ProfileId;

    public function createResetTokenId(string|Uuid|null $uuid = null): ResetTokenId;

    public function createSessionId(string|Uuid|null $uuid = null): SessionId;

    public function createVerificationTokenId(string|Uuid|null $uuid = null): VerificationTokenId;
}
