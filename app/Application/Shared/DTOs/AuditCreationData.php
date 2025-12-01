<?php 
declare(strict_types=1);

namespace App\Application\Shared\DTOs;

use App\Domain\Shared\ValueObjects\DeviceInfo;
use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\Timezone;
use App\Domain\Shared\ValueObjects\UserAgent;
use App\Domain\Shared\ValueObjects\UserId;
use DateTimeImmutable;

final class AuditCreationData implements AuditDataInterface
{
    public function __construct(
        public readonly ?UserId $userId,
        public readonly ?IpAddress $ipAddress,
        public readonly ?Timezone $timezone = null,
        public readonly ?UserAgent $userAgent = null,
        public readonly ?DeviceInfo $deviceInfo = null,
        public readonly ?DateTimeImmutable $timestamp = null,
    ) {}
}
