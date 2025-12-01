<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

interface IdentityInterface
{
    public function __toString(): string;
    
    public function equals(object $other): bool;
}
