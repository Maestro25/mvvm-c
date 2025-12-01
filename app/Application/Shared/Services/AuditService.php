<?php
declare(strict_types=1);

namespace App\Application\Shared\Services;

use App\Application\Shared\DTOs\AuditCreationData;
use App\Domain\Shared\ValueObjects\UserId;
use Psr\Http\Message\ServerRequestInterface;

final class AuditService
{
    public function __construct(
        private readonly UserContextExtractor $contextExtractor
    ) {}

    /**
     * Creates AuditCreationData DTO by extracting and generating audit info.
     */
    public function createAuditData(ServerRequestInterface $request, ?UserId $currentUserId): AuditCreationData
    {
        // Extract user context VOs from request and user ID
        $ipAddress = $this->contextExtractor->extractIpAddress();
        $userAgent = $this->contextExtractor->extractUserAgent();
        $timezone = $this->contextExtractor->extractTimezone();
        $deviceInfo = $this->contextExtractor->extractDeviceInfo();

        // Use AuditCreationDataGenerator with extracted VOs
        $generator = new AuditCreationDataGenerator(
            $ipAddress,
            $userAgent,
            $timezone,
            $currentUserId,
            $deviceInfo
        );

        return $generator->generate();
    }
}
