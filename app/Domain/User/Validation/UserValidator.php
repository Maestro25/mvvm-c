<?php
declare(strict_types=1);

namespace App\Domain\User\Validation;


use App\Domain\Shared\Validation\Rules\NotEmptyRule;
use App\Domain\Shared\Validation\Rules\PatternRule;
use App\Domain\Shared\Validation\Rules\EnumRule;
use App\Domain\Shared\Validation\Rules\ArrayKeysRule;
use App\Domain\User\Entities\User;
use App\Domain\User\Enums\UserStatus;
use App\Domain\Auth\RBAC\Enums\UserRole;
use App\Domain\Shared\Validation\Traits\ValidationGuard;
use App\Domain\Shared\Validation\ValidationRuleInterface;

final class UserValidator
{

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    protected function getValidationRules(): array
    {
        return [
            'username' => new PatternRule(
                '/^[a-zA-Z0-9_]{3,20}$/',
                'Username must be 3-20 chars, alphanumeric or underscores only.'
            ),
            'email' => new PatternRule(
                '/^[\w.+-]+@\w+\.\w+$/',
                'Invalid email format.'
            ),
            'passwordHash' => new NotEmptyRule('Password hash must not be empty.'),
            'status' => new EnumRule(
                [UserStatus::ACTIVE, UserStatus::INACTIVE],
                'User status is invalid.'
            ),
            'roles' => new ArrayKeysRule(
                array_map(fn(UserRole $r) => $r->value, UserRole::cases()),
                'Roles contain invalid values.'
            ),
            'createdInfo' => new NotEmptyRule('Created info must not be empty.'),
            'updatedInfo' => new NotEmptyRule('Updated info must not be empty.'),
            // deletedInfo can be nullable, so no rule or nullable rule can be added as needed
        ];
    }

    protected function getValidationData(): array
    {
        // Convert value objects & enums to scalar/string for validation
        return [
            'username' => (string)$this->user->getUsername(),
            'email' => (string)$this->user->getEmail(),
            'passwordHash' => (string)$this->user->getPasswordHash(),
            'status' => $this->user->getStatus(),
            'roles' => $this->user->getRoles(),
            'createdInfo' => $this->user->getCreatedInfo(),
            'updatedInfo' => $this->user->getUpdatedInfo(),
            // 'deletedInfo' can be included if needed with nullable validation handling
        ];
    }
}
