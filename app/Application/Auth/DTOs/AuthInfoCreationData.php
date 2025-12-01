<?php
declare(strict_types=1);

namespace App\Application\Auth\DTOs;

use App\Application\Shared\DTOs\EntityCreationDataInterface;
use App\Domain\User\ValueObjects\FailedLoginAttempts;
use App\Domain\Auth\Entities\PasswordResetToken;
use App\Domain\Auth\Entities\EmailVerificationToken;
use App\Domain\Auth\Entities\RememberMeToken;
use DateTimeImmutable;

final class AuthInfoCreationData implements EntityCreationDataInterface
{
    public function __construct(
        public readonly FailedLoginAttempts $failedLoginAttempts,
        public readonly ?DateTimeImmutable $lastFailedLoginAt = null,
        public readonly ?DateTimeImmutable $lockedUntil = null,
        public readonly ?PasswordResetToken $passwordResetToken = null,
        public readonly ?EmailVerificationToken $emailVerificationToken = null,
        public readonly ?RememberMeToken $rememberMeToken = null,
        public readonly ?DateTimeImmutable $lastLoginAt = null
    ) {}
}
