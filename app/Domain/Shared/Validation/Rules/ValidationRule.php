<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

abstract class ValidationRule implements ValidationRuleInterface
{
    protected string $message;

    public function __construct(string $message = '')
    {
        $this->message = $message ?: $this->defaultMessage();
    }

    abstract protected function defaultMessage(): string;

    public function getMessage(): string
    {
        return $this->message;
    }

    abstract public function validate(mixed $value): bool;
}
