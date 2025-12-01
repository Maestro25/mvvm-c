<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class BooleanRule extends ValidationRule
{
    private bool $allowNull;
    public function __construct(string $message = '', bool $allowNull = false)
    {
        $this->allowNull = $allowNull;
        parent::__construct($message);
    }
    protected function defaultMessage(): string
    {
        return 'Value must be boolean.';
    }

    public function validate(mixed $value): bool
    {
        return ($value === null && $this->allowNull) || is_bool($value);
    }

}