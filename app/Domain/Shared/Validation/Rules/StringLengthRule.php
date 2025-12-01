<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class StringLengthRule extends ValidationRule
{
    private int $min;
    private int $max;

    public function __construct(int $min = 0, int $max = PHP_INT_MAX, string $message = '')
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return "String length must be between {$this->min} and {$this->max} characters.";
    }

    public function validate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $len = mb_strlen($value);
        return $len >= $this->min && $len <= $this->max;
    }

}
