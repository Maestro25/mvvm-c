<?php
declare(strict_types=1);

namespace App\Application\Shared\Services;

use App\Domain\Shared\ValueObjects\IpAddress;
use App\Domain\Shared\ValueObjects\UserAgent;
use App\Domain\Shared\ValueObjects\Timezone;
use App\Domain\Shared\ValueObjects\DeviceInfo;
use App\Domain\Shared\ValueObjects\UserId;
use Psr\Http\Message\ServerRequestInterface;
use App\Application\Shared\DTOs\AuditCreationData;
use DateTimeImmutable;
use Jenssegers\Agent\Agent;

final class UserContextExtractor
{
    public function __construct(
        private readonly ServerRequestInterface $request,
        private readonly ?UserId $currentUserId
    ) {
    }

    public function extractIpAddress(): IpAddress
    {
        $ipString = $this->request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';
        return new IpAddress($ipString);
    }

    public function extractUserAgent(): UserAgent
    {
        $userAgentString = $this->request->getHeaderLine('User-Agent');
        return new UserAgent($userAgentString ?: 'unknown');
    }

    public function extractTimezone(): Timezone
    {
        // Could get from user profile, request header, or default config; defaulting to UTC here.
        return new Timezone('UTC');
    }

    public function extractDeviceInfo(): ?DeviceInfo
    {
        $agent = new Agent();
        $agent->setUserAgent($this->request->getHeaderLine('User-Agent'));

        $deviceType = 'Desktop';
        if ($agent->isMobile()) {
            $deviceType = 'Mobile';
        } elseif ($agent->isTablet()) {
            $deviceType = 'Tablet';
        }

        $browser = $agent->browser();
        $platform = $agent->platform();

        $deviceInfoString = sprintf('%s on %s using %s', $deviceType, $platform, $browser);

        return new DeviceInfo($deviceInfoString);
    }

    public function extractAuditCreationData(): AuditCreationData
    {
        $ipAddress = $this->extractIpAddress();
        $userAgent = $this->extractUserAgent();
        $timezone = $this->extractTimezone();
        $deviceInfo = $this->extractDeviceInfo();
        $timestamp = new DateTimeImmutable('now', $timezone->getDateTimeZone());

        return new AuditCreationData(
            timestamp: $timestamp,
            timezone: $timezone,
            userId: $this->currentUserId,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            deviceInfo: $deviceInfo
        );
    }
}
