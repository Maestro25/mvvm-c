<?php
declare(strict_types=1);

namespace App\Domain\Job\ValueObjects;

final readonly class JobName
{
    public function __construct(public string $value)
    {
        $this->guardAgainstEmpty($value);
        $this->guardAgainstInvalidCharacters($value);
    }

    private function guardAgainstEmpty(string $value): void
    {
        if (trim($value) === '') {
            throw new \InvalidArgumentException('Job name cannot be empty.');
        }
    }

    private function guardAgainstInvalidCharacters(string $value): void
    {
        // Example: allow letters, digits, spaces, underscores, dashes
        if (!preg_match('/^[\w\s\-]+$/u', $value)) {
            throw new \InvalidArgumentException('Job name contains invalid characters.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
