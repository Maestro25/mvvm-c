<?php
declare(strict_types=1);

namespace App\Domain\User\Entities;

use App\Domain\Auth\Entities\EmailVerificationToken;
use App\Domain\Auth\Entities\PasswordResetToken;
use App\Domain\Auth\Entities\RememberMeToken;
use App\Domain\Shared\Entities\Entity;
use App\Domain\Shared\ValueObjects\AuthInfoId;
use App\Domain\ValueObjects\UserId;
use App\Domain\User\ValueObjects\FailedLoginAttempts;
use App\Domain\User\ValueObjects\AuditInfo;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Represents user's authentication-related information including lock status,
 * failed login tracking, and various tokens with explicit immutability.
 */
final class AuthInfo extends Entity
{
    public function __construct(
        AuthInfoId $id,
        private readonly FailedLoginAttempts $failedLoginAttempts,
        private readonly ?DateTimeImmutable $lastFailedLoginAt = null,
        private readonly ?DateTimeImmutable $lockedUntil = null,
        private readonly ?PasswordResetToken $passwordResetToken = null,
        private readonly ?EmailVerificationToken $emailVerificationToken = null,
        private readonly ?RememberMeToken $rememberMeToken = null,
        private readonly ?DateTimeImmutable $lastLoginAt = null
    ) {
        if ($failedLoginAttempts->getValue() < 0) {
            throw new InvalidArgumentException('Failed login attempts cannot be negative.');
        }
        parent::__construct($id);
    }

    public function getId(): AuthInfoId
    {
        return $this->id;
    }

    public function getFailedLoginAttempts(): FailedLoginAttempts
    {
        return $this->failedLoginAttempts;
    }

    public function getLastFailedLoginAt(): ?DateTimeImmutable
    {
        return $this->lastFailedLoginAt;
    }

    public function getLockedUntil(): ?DateTimeImmutable
    {
        return $this->lockedUntil;
    }

    public function getPasswordResetToken(): ?PasswordResetToken
    {
        return $this->passwordResetToken;
    }

    public function getEmailVerificationToken(): ?EmailVerificationToken
    {
        return $this->emailVerificationToken;
    }

    public function getRememberMeToken(): ?RememberMeToken
    {
        return $this->rememberMeToken;
    }

    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    // Business methods returning new instances to maintain immutability

    public function recordFailedLogin(DateTimeImmutable $failedAt): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: $this->failedLoginAttempts->increment(),
            lastFailedLoginAt: $failedAt,
            lockedUntil: $this->lockedUntil,
            passwordResetToken: $this->passwordResetToken,
            emailVerificationToken: $this->emailVerificationToken,
            rememberMeToken: $this->rememberMeToken,
            lastLoginAt: $this->lastLoginAt,
        );
    }

    public function resetFailedLogin(): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: FailedLoginAttempts::zero(),
            lastFailedLoginAt: null,
            lockedUntil: $this->lockedUntil,
            passwordResetToken: $this->passwordResetToken,
            emailVerificationToken: $this->emailVerificationToken,
            rememberMeToken: $this->rememberMeToken,
            lastLoginAt: $this->lastLoginAt,
        );
    }

    public function lockUntil(DateTimeImmutable $lockUntil): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: $this->failedLoginAttempts,
            lastFailedLoginAt: $this->lastFailedLoginAt,
            lockedUntil: $lockUntil,
            passwordResetToken: $this->passwordResetToken,
            emailVerificationToken: $this->emailVerificationToken,
            rememberMeToken: $this->rememberMeToken,
            lastLoginAt: $this->lastLoginAt,
        );
    }

    public function withPasswordResetToken(PasswordResetToken $token): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: $this->failedLoginAttempts,
            lastFailedLoginAt: $this->lastFailedLoginAt,
            lockedUntil: $this->lockedUntil,
            passwordResetToken: $token,
            emailVerificationToken: $this->emailVerificationToken,
            rememberMeToken: $this->rememberMeToken,
            lastLoginAt: $this->lastLoginAt,
        );
    }

    public function withEmailVerificationToken(EmailVerificationToken $token): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: $this->failedLoginAttempts,
            lastFailedLoginAt: $this->lastFailedLoginAt,
            lockedUntil: $this->lockedUntil,
            passwordResetToken: $this->passwordResetToken,
            emailVerificationToken: $token,
            rememberMeToken: $this->rememberMeToken,
            lastLoginAt: $this->lastLoginAt,
        );
    }

    public function withRememberMeToken(RememberMeToken $token): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: $this->failedLoginAttempts,
            lastFailedLoginAt: $this->lastFailedLoginAt,
            lockedUntil: $this->lockedUntil,
            passwordResetToken: $this->passwordResetToken,
            emailVerificationToken: $this->emailVerificationToken,
            rememberMeToken: $token,
            lastLoginAt: $this->lastLoginAt,
        );
    }

    public function withLastLoginAt(DateTimeImmutable $lastLoginAt): self
    {
        return new self(
            $this->id,
            failedLoginAttempts: $this->failedLoginAttempts,
            lastFailedLoginAt: $this->lastFailedLoginAt,
            lockedUntil: $this->lockedUntil,
            passwordResetToken: $this->passwordResetToken,
            emailVerificationToken: $this->emailVerificationToken,
            rememberMeToken: $this->rememberMeToken,
            lastLoginAt: $lastLoginAt,
        );
    }

    public function __toString(): string
    {
        return sprintf(
            'Failed: %d, LastFail: %s, LockedUntil: %s, LastLogin: %s',
            $this->failedLoginAttempts->getValue(),
            $this->lastFailedLoginAt?->format('c') ?? 'null',
            $this->lockedUntil?->format('c') ?? 'null',
            $this->lastLoginAt?->format('c') ?? 'null'
        );
    }
}
