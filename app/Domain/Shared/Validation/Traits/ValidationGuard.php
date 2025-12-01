<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Traits;

use App\Domain\Shared\Validation\Exceptions\ValidationException;
use App\Domain\Shared\Validation\Rules\ValidationRuleInterface;
use Psr\Log\LoggerInterface;

trait ValidationGuard
{
    /**
     * @var array<string, string[]>
     */
    private array $errors = [];

    /**
     * PSR-3 Logger instance.
     */
    protected ?LoggerInterface $logger = null;

    /**
     * Set the logger instance.
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Validates the entity data according to defined rules.
     *
     * Uses external ValidationRuleInterface implementations for rules.
     *
     * @throws ValidationException if validation fails
     */
    public function validate(): bool
    {
        $rules = $this->getValidationRules();
        $data = $this->getValidationData();

        $errors = [];

        if ($this->logger !== null) {
            $this->logger->debug('Validation data for ' . static::class, ['data' => $data]);
        }

        foreach ($rules as $field => $rule) {
            if (!array_key_exists($field, $data)) {
                $errors[$field][] = "Missing data for '{$field}'.";
                continue;
            }

            $value = $data[$field];

            if (!$rule instanceof ValidationRuleInterface) {
                throw new \LogicException("Rule for '{$field}' must implement ValidationRuleInterface.");
            }

            if (!$rule->validate($value)) {
                $errors[$field][] = $rule->getMessage();
            }
        }

        if (!empty($errors)) {
            if ($this->logger !== null) {
                foreach ($errors as $field => $messages) {
                    foreach ($messages as $message) {
                        $this->logger->error(
                            sprintf('Validation failed in %s: %s - %s', static::class, $field, $message),
                            [
                                'field' => $field,
                                'value' => $data[$field] ?? null,
                                'value_type' => isset($data[$field]) ? gettype($data[$field]) : 'undefined',
                                'class' => static::class,
                            ]
                        );
                    }
                }
            }

            // Throw the enhanced exception with source context for better error info
            throw new ValidationException($errors, static::class . '::validate()');
        }

        return true;
    }



    /**
     * Returns an associative array of validation rules.
     * Override this in using class/trait.
     *
     * @return array<string, ValidationRuleInterface>
     */
    abstract protected function getValidationRules(): array;

    /**
     * Returns an associative array of data to validate.
     * Override this in using class/trait.
     *
     * @return array<string, mixed>
     */
    abstract protected function getValidationData(): array;
}
