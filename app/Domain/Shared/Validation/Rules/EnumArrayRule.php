<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;


use BackedEnum;

/**
 * Validates that all elements of an array are instances of the specified Enum class.
 */
final class EnumArrayRule extends ValidationRule
{
    private string $enumClass;

    public function __construct(string $enumClass, string $message = '')
    {
        $this->enumClass = $enumClass;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Array contains invalid enum instances.';
    }

    public function validate(mixed $value): bool
{
    if ($value === null) { // Accept null if desired, otherwise return false
        return true; 
    }
    return is_object($value) && is_a($value, $this->enumClass);
}

}
