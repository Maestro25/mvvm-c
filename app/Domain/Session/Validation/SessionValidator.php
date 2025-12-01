<?php
declare(strict_types=1);

namespace App\Domain\Session\Validation;

use App\Domain\Session\Entities\SessionInterface;
use App\Domain\Session\Validation\Traits\SessionValidationGuard;
use App\Domain\Session\Enums\SessionStatus;

final class SessionValidator implements SessionValidatorInterface
{
    use SessionValidationGuard;

    private ?SessionInterface $session = null;
    private array $validationErrors = [];

    // Required by the trait to get the current session entity
    protected function getSession(): SessionInterface
    {
        if ($this->session === null) {
            throw new \LogicException('No session set for validation.');
        }
        return $this->session;
    }

    /**
     * Validate the given Session entity.
     */
    public function validate(SessionInterface $session): bool
    {
        $this->session = $session;
        $this->validationErrors = [];

        // Validate based on trait rules
        if (!$this->validateSession()) {
            return false;
        }

        // Additional domain-specific validations
        if ($session->isExpired()) {
            $this->validationErrors[] = 'Session has expired.';
        }

        if ($session->getStatus() !== SessionStatus::ACTIVE) {
            $this->validationErrors[] = 'Session is not active.';
        }

        if (!$this->validateClientMetadata($session)) {
            $this->validationErrors[] = 'Client metadata mismatch.';
        }

        return empty($this->validationErrors);
    }

    /**
     * Returns collected validation errors after validate().
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Validate session data according to trait-defined rules.
     */
    private function validateSession(): bool
    {
        $rules = $this->getValidationRules();
        $data = $this->getValidationData();

        $passed = true;
        foreach ($rules as $field => $rule) {
            if (!$rule->validate($data[$field] ?? null)) {
                $this->validationErrors[] = $rule->getErrorMessage();
                $passed = false;
            }
        }

        return $passed;
    }

    /**
     * Placeholder for client metadata validation logic.
     */
    private function validateClientMetadata(SessionInterface $session): bool
    {
        // Implement detailed client metadata validation as needed
        return true;
    }
}
