<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;


use BackedEnum;

/**
 * Validates that a single value is an instance of the specified Enum class.
 */
final class EnumInstanceRule extends ValidationRule
{
    private string $enumClass;

    public function __construct(string $enumClass, string $message = '')
    {
        $this->enumClass = $enumClass;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Value is not a valid enum instance.';
    }

    public function validate(mixed $value): bool
    {
        if (!is_object($value)) {
            return false;
        }
        if (!is_a($value, $this->enumClass)) {
            return false;
        }
        return true;
    }
}

/**
 * Validates that all elements of an array are instances of the specified Enum class.
 */
final class EnumArrayRule extends ValidationRule
{
    private string $enumClass;

    private bool $allowNull;

    public function __construct(string $enumClass, string $message = '', bool $allowNull = false)
    {
        $this->allowNull = $allowNull;
        $this->enumClass = $enumClass;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Array contains invalid enum instances.';
    }

    public function validate(mixed $value): bool
    {
        if ($value === null && $this->allowNull) {
            return true;
        }
        return is_object($value) && is_a($value, $this->enumClass);
    }

}
