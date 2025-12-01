<?php
declare(strict_types=1);

namespace App\Domain\Shared\Factories;

use App\Domain\Shared\ValueObjects\AuthInfoId;
use App\Domain\Shared\ValueObjects\Uuid;
use App\Domain\Shared\ValueObjects\GuestId;
use App\Domain\Shared\ValueObjects\JobId;
use App\Domain\Shared\ValueObjects\UserId;
use App\Domain\Shared\ValueObjects\PasswordHistoryId;
use App\Domain\Shared\ValueObjects\PermissionId;
use App\Domain\Shared\ValueObjects\ProfileId;
use App\Domain\Shared\ValueObjects\ResetTokenId;
use App\Domain\Shared\ValueObjects\RoleId;
use App\Domain\Shared\ValueObjects\SessionId;
use App\Domain\Shared\ValueObjects\VerificationTokenId;

use Ramsey\Uuid\UuidFactory;

final class IdentityFactory implements IdentityFactoryInterface
{
    public function __construct(private readonly UuidFactory $uuidFactory)
    {
    }

    /**
     * Create new UserId, generate UUID if none provided.
     */
    public function createUserId(string|Uuid|null $uuid = null): UserId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new UserId($uuidObj);
    }

    /**
     * Create new RoleId, generate UUID if none provided.
     */
    public function createRoleId(string|Uuid|null $uuid = null): RoleId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new RoleId($uuidObj);
    }
    public function createPermissionId(string|Uuid|null $uuid = null): PermissionId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new PermissionId($uuidObj);
    }
    public function createGuestId(string|Uuid|null $uuid = null): GuestId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new GuestId($uuidObj);
    }
    public function createPasswordHistoryId(string|Uuid|null $uuid = null): PasswordHistoryId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new PasswordHistoryId($uuidObj);
    }
    public function createProfileId(string|Uuid|null $uuid = null): ProfileId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new ProfileId($uuidObj);
    }
    public function createResetTokenId(string|Uuid|null $uuid = null): ResetTokenId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new ResetTokenId($uuidObj);
    }
    public function createSessionId(string|Uuid|null $uuid = null): SessionId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new SessionId($uuidObj);
    }
    public function createAuthInfoId(string|Uuid|null $uuid = null): AuthInfoId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new AuthInfoId($uuidObj);
    }
    public function createJobId(string|Uuid|null $uuid = null): JobId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new JobId($uuidObj);
    }

    /**
     * Create new VerificationTokenId, generate UUID if none provided.
     */
    public function createVerificationTokenId(string|Uuid|null $uuid = null): VerificationTokenId
    {
        $uuidObj = $this->resolveUuid($uuid);
        return new VerificationTokenId($uuidObj);
    }

    /**
     * Helper function to resolve or generate Uuid VO.
     */
    private function resolveUuid(string|Uuid|null $uuid): Uuid
    {
        if ($uuid instanceof Uuid) {
            return $uuid;
        }
        if (is_string($uuid)) {
            return new Uuid($uuid);
        }
        // Generate a new UUID using Ramsey factory
        $generated = $this->uuidFactory->uuid4();
        return new Uuid($generated);
    }
}
