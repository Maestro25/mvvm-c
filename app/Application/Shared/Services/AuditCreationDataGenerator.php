<?php
declare(strict_types=1);

namespace App\Application\Shared\Services;

use App\Application\Shared\DTOs\AuditCreationData;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\UserAgent;
use App\Domain\Shared\ValueObjects\Timezone;
use App\Domain\Shared\ValueObjects\DeviceInfo;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final class AuditCreationDataGenerator
{
    public function __construct(
        private readonly IpAddress $ipAddress,
        private readonly UserAgent $userAgent,
        private readonly Timezone $timeZone,
        private readonly ?UserId $currentUserId,
        private readonly ?DeviceInfo $deviceInfo = null
    ) {}

    public function generate(): AuditCreationData
    {
        $timestamp = new DateTimeImmutable('now', $this->timeZone->getDateTimeZone());

        return new AuditCreationData(
            timestamp: $timestamp,
            timezone: $this->timeZone,
            userId: $this->currentUserId,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
            deviceInfo: $this->deviceInfo
        );
    }
}
