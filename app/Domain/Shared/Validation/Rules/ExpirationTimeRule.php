<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

use App\Domain\Shared\ValueObjects\ExpirationTime;

final class ExpirationTimeRule extends ValidationRule
{
    private bool $allowNull;

    public function __construct(string $message = '', bool $allowNull = false)
    {
        $this->allowNull = $allowNull;
        parent::__construct($message);
    }
    protected function defaultMessage(): string
    {
        return 'Expiration time is invalid or expired.';
    }

    public function validate(mixed $value): bool
    {
        if ($value === null && $this->allowNull) {
            return true;
        }
        return $value instanceof ExpirationTime && !$value->isExpired();
    }
}

