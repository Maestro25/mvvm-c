<?php
declare(strict_types=1);

namespace App\Domain\Shared\ValueObjects;

use DateTimeImmutable;

/**
 * Audit metadata Value Object capturing creation,
 * update, and deletion audit data with type safety.
 */
final class AuditInfo extends ValueObject
{
    public function __construct(
        public ?DateTimeImmutable $createdAt = null,
        public ?UserId $createdBy = null,
        public ?IpAddress $createdIp = null,
        public ?DateTimeImmutable $updatedAt = null,
        public ?UserId $updatedBy = null,
        public ?IpAddress $updatedIp = null,
        public ?DateTimeImmutable $deletedAt = null,
        public ?UserId $deletedBy = null,
        public ?IpAddress $deletedIp = null,
        public ?UserAgent $userAgent = null,
        public ?DeviceInfo $deviceInfo = null,
    ) {
        // No need to call parent constructor if none required
    }

    protected function getAtomicValues(): array
    {
        return [
            $this->createdAt,
            $this->createdBy,
            $this->createdIp,
            $this->updatedAt,
            $this->updatedBy,
            $this->updatedIp,
            $this->deletedAt,
            $this->deletedBy,
            $this->deletedIp,
            $this->userAgent,
            $this->deviceInfo,
        ];
    }

    public function __toString(): string
    {
        return sprintf(
            'Created %s by %s, Updated %s by %s, Deleted %s by %s',
            $this->createdAt?->format('Y-m-d H:i:s') ?? 'N/A',
            $this->createdBy?->__toString() ?? 'N/A',
            $this->updatedAt?->format('Y-m-d H:i:s') ?? 'N/A',
            $this->updatedBy?->__toString() ?? 'N/A',
            $this->deletedAt?->format('Y-m-d H:i:s') ?? 'N/A',
            $this->deletedBy?->__toString() ?? 'N/A',
        );
    }
}
