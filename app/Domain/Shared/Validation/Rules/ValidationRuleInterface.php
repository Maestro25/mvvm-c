<?php
declare(strict_types=1);

namespace App\Domain\Shared\Validation\Rules;

interface ValidationRuleInterface
{
    public function validate(mixed $value): bool;
    public function getMessage(): string;
}
