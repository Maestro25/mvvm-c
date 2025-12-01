<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class EnumRule extends ValidationRule
{
    /**
     * @var array<int|string>
     */
    private array $allowedValues;

    public function __construct(array $allowedValues, string $message = '')
    {
        $this->allowedValues = $allowedValues;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'Value is not an allowed option.';
    }

    public function validate(mixed $value): bool
    {
        return in_array($value, $this->allowedValues, true);
    }
}
