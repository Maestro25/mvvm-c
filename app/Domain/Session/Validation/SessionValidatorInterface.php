<?php
declare(strict_types=1);

namespace App\Domain\Session\Validation;

use App\Domain\Session\Entities\SessionInterface;

interface SessionValidatorInterface
{
    /**
     * Validates the session entity.
     *
     * @param SessionInterface $session
     * @return bool True if valid, false otherwise.
     */
    public function validate(SessionInterface $session): bool;

    /**
     * Returns validation failure error messages.
     *
     * @return string[]
     */
    public function getValidationErrors(): array;
}
