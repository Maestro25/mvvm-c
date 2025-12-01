<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class ArrayKeysRule extends ValidationRule
{
    /** @var array<string|int> */
    private array $allowedValues;

    /**
     * @param array<string|int> $allowedValues
     * @param string $message
     */
    public function __construct(array $allowedValues, string $message = '')
    {
        $this->allowedValues = $allowedValues;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Array contains disallowed values.';
    }

    public function validate(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (empty($value)) {  // Optional acceptance of empty arrays
            return true;
        }
        foreach ($value as $val) {
            if (!in_array($val, $this->allowedValues, true)) {
                return false;
            }
        }
        return true;
    }

}
