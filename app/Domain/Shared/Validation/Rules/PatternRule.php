<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class PatternRule extends ValidationRule
{
    private string $pattern;
    private bool $allowNull;

    public function __construct(string $pattern, string $message = '', bool $allowNull = false)
    {
        $this->pattern = $pattern;
        $this->allowNull = $allowNull;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Value does not match the required pattern.';
    }

    public function validate(mixed $value): bool
    {
        if ($value === null && $this->allowNull) {
            return true;
        }
        return is_string($value) && preg_match($this->pattern, $value) === 1;
    }
}

