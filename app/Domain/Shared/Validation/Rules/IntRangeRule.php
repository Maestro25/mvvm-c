<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class IntRangeRule extends ValidationRule
{
    private int $min;
    private int $max;

    public function __construct(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX, string $message = '')
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return "Integer value must be between {$this->min} and {$this->max}.";
    }

    public function validate(mixed $value): bool
{
    return is_int($value) && $value >= $this->min && $value <= $this->max;
}

}
