<?php
declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use App\Domain\User\Exceptions\InvalidAddressException;
use App\Domain\Shared\ValueObjects\ValueObject;

final class Address extends ValueObject
{
    private readonly ?string $addressLine1;
    private readonly ?string $addressLine2;
    private readonly ?string $city;
    private readonly ?string $state;
    private readonly ?string $postalCode;
    private readonly ?string $country;

    public function __construct(
        ?string $addressLine1,
        ?string $addressLine2,
        ?string $city,
        ?string $state,
        ?string $postalCode,
        ?string $country
    ) {
        $this->addressLine1 = $this->validateString($addressLine1, 255, 'Address Line 1');
        $this->addressLine2 = $this->validateString($addressLine2, 255, 'Address Line 2');
        $this->city = $this->validateString($city, 100, 'City');
        $this->state = $this->validateString($state, 100, 'State');
        $this->postalCode = $this->validateString($postalCode, 20, 'Postal Code');
        $this->country = $this->validateString($country, 100, 'Country');
    }

    private function validateString(?string $value, int $maxLength, string $fieldName): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        $length = mb_strlen($trimmed);
        if ($length === 0) {
            return null;
        }
        if ($length > $maxLength) {
            throw InvalidAddressException::fieldLengthViolation($fieldName, $length, $maxLength);
        }
        return $trimmed;
    }

    protected function getAtomicValues(): array
    {
        return [
            $this->addressLine1,
            $this->addressLine2,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country
        ];
    }

    // Public getters for all properties for external access

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function __toString(): string
    {
        return implode(', ', array_filter([
            $this->addressLine1,
            $this->addressLine2,
            $this->city,
            $this->state,
            $this->postalCode,
            $this->country,
        ]));
    }
}
