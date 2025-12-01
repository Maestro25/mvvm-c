<?php
declare(strict_types=1);

namespace App\Domain\Shared\Factories;

use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Application\Shared\DTOs\AuditCreationData;
use DateTimeImmutable;

interface AuditInfoFactoryInterface
{
    public function createForCreation(IdentityInterface $id, AuditCreationData $data): AuditInfo;

    public function createForUpdate(IdentityInterface $id, AuditCreationData $data): AuditInfo;

    public function createForDeletion(IdentityInterface $id, AuditCreationData $data): AuditInfo;
}