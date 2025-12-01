<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class NotEmptyRule extends ValidationRule
{
    private bool $allowNull;

    public function __construct(string $message = '', bool $allowNull = false)
    {
        $this->allowNull = $allowNull;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Value must not be empty.';
    }

    public function validate(mixed $value): bool
    {
        if ($value === null && $this->allowNull) {
            return true; // allow null if configured
        }

        if ($value === null) {
            return false; // reject null if not allowed
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return count($value) > 0;
        }

        if (is_bool($value)) {
            return $value === true;
        }

        return true;
    }
}
