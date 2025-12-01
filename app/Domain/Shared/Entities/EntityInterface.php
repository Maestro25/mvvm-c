<?php
declare(strict_types=1);

namespace App\Domain\Shared\Entities;

use App\Domain\Shared\ValueObjects\IdentityInterface;

interface EntityInterface
{
    public function getId(): IdentityInterface;

    public function equals(EntityInterface $other): bool;
}
