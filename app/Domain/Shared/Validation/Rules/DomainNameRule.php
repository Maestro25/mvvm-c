<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

final class DomainNameRule extends ValidationRule
{
    private bool $allowNull;

    public function __construct(string $message = '', bool $allowNull = false)
    {
        $this->allowNull = $allowNull;
        parent::__construct($message);
    }

    protected function defaultMessage(): string
    {
        return 'The domain name is invalid.';
    }

    public function validate(mixed $value): bool
    {
        if ($value === null && $this->allowNull) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        // Checks format according to RFC standards for domain names
        $pattern = '/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i';

        return (bool)preg_match($pattern, $value);
    }
}
