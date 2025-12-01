<?php
declare(strict_types=1);

namespace App\Domain\Shared\Factories;

use App\Domain\Shared\ValueObjects\AuditInfo;
use App\Domain\Shared\ValueObjects\IdentityInterface;
use App\Application\Shared\DTOs\AuditCreationData;
use DateTimeImmutable;
use DateTimeZone;

final class AuditInfoFactory implements AuditInfoFactoryInterface
{
    public function createForCreation(IdentityInterface $id, AuditCreationData $data): AuditInfo
    {
        $timezone = $this->convertToDateTimeZone($data->timezone);
        $timestamp = $this->normalizeTimestamp($data->timestamp, $timezone);

        return new AuditInfo(
            createdAt: $timestamp,
            createdBy: $data->userId,
            createdIp: $data->ipAddress,
            userAgent: $data->userAgent,
            deviceInfo: $data->deviceInfo
        );
    }

    public function createForUpdate(IdentityInterface $id, AuditCreationData $data): AuditInfo
    {
        $timezone = $this->convertToDateTimeZone($data->timezone);
        $timestamp = $this->normalizeTimestamp($data->timestamp, $timezone);

        return new AuditInfo(
            updatedAt: $timestamp,
            updatedBy: $data->userId,
            updatedIp: $data->ipAddress,
            userAgent: $data->userAgent,
            deviceInfo: $data->deviceInfo
        );
    }

    public function createForDeletion(IdentityInterface $id, AuditCreationData $data): AuditInfo
    {
        $timezone = $this->convertToDateTimeZone($data->timezone);
        $timestamp = $this->normalizeTimestamp($data->timestamp, $timezone);

        return new AuditInfo(
            deletedAt: $timestamp,
            deletedBy: $data->userId,
            deletedIp: $data->ipAddress,
            userAgent: $data->userAgent,
            deviceInfo: $data->deviceInfo
        );
    }

    private function convertToDateTimeZone(?object $timezone): ?\DateTimeZone
    {
        if ($timezone === null) {
            return null;
        }
        if (method_exists($timezone, 'getDateTimeZone')) {
            return $timezone->getDateTimeZone();
        }
        throw new \InvalidArgumentException('Cannot convert timezone value object to native DateTimeZone');
    }



    private function normalizeTimestamp(?DateTimeImmutable $timestamp, ?DateTimeZone $timezone): DateTimeImmutable
    {
        $timezoneObj = $timezone ?? new DateTimeZone('UTC');
        $time = $timestamp ?? new DateTimeImmutable();

        return $time->setTimezone($timezoneObj);
    }
}
