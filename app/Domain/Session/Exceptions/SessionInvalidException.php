<?php
declare(strict_types=1);

namespace App\Application\Session\Validation\Exceptions;

use RuntimeException;

final class SessionInvalidException extends RuntimeException
{
    /**
     * Detailed validation errors explaining why the session is invalid.
     * 
     * @var string[]
     */
    private array $validationErrors;

    /**
     * @param string|string[] $message A descriptive message or array of errors
     * @param int $code Optional exception code, defaults to 0
     * @param \Throwable|null $previous Previous exception for chaining
     */
    public function __construct(string|array $message = "Session validation failed.", int $code = 0, ?\Throwable $previous = null)
    {
        if (is_array($message)) {
            $this->validationErrors = $message;
            $message = implode("; ", $message);
        } else {
            $this->validationErrors = [$message];
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns all validation errors responsible for the exception.
     * 
     * @return string[]
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
