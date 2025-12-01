<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Exceptions;

use Exception;

final class ValidationException extends Exception
{
    private array $errors;
    private ?string $sourceContext;

    /**
     * @param array $errors Validation error details per field
     * @param string|null $sourceContext Optional context about where validation failed (e.g. class or method name)
     */
    public function __construct(array $errors, ?string $sourceContext = null)
    {
        $this->errors = $errors;
        $this->sourceContext = $sourceContext;

        $message = 'Validation failed';
        if ($sourceContext !== null) {
            $message .= " in {$sourceContext}";
        }

        parent::__construct($message);
    }

    /**
     * Returns detailed validation errors
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns source context string if provided
     *
     * @return string|null
     */
    public function getSourceContext(): ?string
    {
        return $this->sourceContext;
    }

    /**
     * Converts exception to detailed string including errors
     *
     * @return string
     */
    public function __toString(): string
    {
        $errorDetails = json_encode($this->errors, JSON_PRETTY_PRINT);
        $context = $this->sourceContext ? " in {$this->sourceContext}" : '';
        return __CLASS__ . ": [{$this->code}]: {$this->message}{$context}\nValidation errors:\n{$errorDetails}\n";
    }
}
