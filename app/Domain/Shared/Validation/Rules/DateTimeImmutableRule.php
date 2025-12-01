<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

use DateTimeImmutable;

final class DateTimeImmutableRule extends ValidationRule
{
    private bool $allowNull;

    public function __construct(string $message = '', bool $allowNull = false)
    {
        $this->allowNull = $allowNull;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'The {attribute} must be a valid DateTimeImmutable object.';
    }

    public function validate(mixed $value): bool
    {
        if ($value === null && $this->allowNull) {
            return true;
        }
        return $value instanceof DateTimeImmutable;
    }
}
